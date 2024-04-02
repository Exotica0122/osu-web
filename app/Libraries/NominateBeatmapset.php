<?php

// Copyright (c) ppy Pty Ltd <contact@ppy.sh>. Licensed under the GNU Affero General Public License v3.0.
// See the LICENCE file in the repository root for full licence text.

declare(strict_types=1);

namespace App\Libraries;

use App\Enums\Ruleset;
use App\Exceptions\InvariantException;
use App\Jobs\Notifications\BeatmapsetNominate;
use App\Models\Beatmapset;
use App\Models\BeatmapsetEvent;
use App\Models\User;
use Ds\Set;

class NominateBeatmapset
{
    /** @var Set<Ruleset> */
    private Set $beatmapRulesets;
    /** @var Set<Ruleset|null> */
    private Set $nominatedRulesets;

    public function __construct(private Beatmapset $beatmapset, private User $user, array $playmodes)
    {
        $this->beatmapRulesets = new Set(array_map(fn ($playmode) => Ruleset::from($playmode), $beatmapset->playmodes()->toArray()));
        $this->nominatedRulesets = new Set(Ruleset::tryFromNames($playmodes));
    }

    public static function requiredNominationsConfig()
    {
        return [
            'main_ruleset' => $GLOBALS['cfg']['osu']['beatmapset']['required_nominations'],
            'non_main_ruleset' => 1,
        ];
    }

    private static function nominationCount(array $nominationsByType, string $type, ?string $mode): int
    {
        return count(array_filter($nominationsByType[$type], fn ($item) => $item === $mode));
    }

    public function handle()
    {
        $this->beatmapset->resetMemoized(); // ensure we're not using cached/stale event data

        $this->assertValidState();

        if ($this->beatmapset->isLegacyNominationMode()) {
            throw new InvariantException();
        }

        $eventParams = $this->nominateRulesets();

        $nomination = $this->beatmapset->beatmapsetNominations()->current()->where('user_id', $this->user->getKey());
        if (!$nomination->exists()) {
            $this->beatmapset->getConnection()->transaction(function () use ($eventParams) {
                $event = $this->beatmapset->events()->create($eventParams);
                $this->beatmapset->beatmapsetNominations()->create([
                    'event_id' => $event->getKey(),
                    'modes' => $eventParams['comment']['modes'] ?? null,
                    'user_id' => $this->user->getKey(),
                ]);

                if ($this->shouldQualify()) {
                    $beatmapset = $this->beatmapset->lockForUpdate()->find($this->beatmapset->getKey());
                    // Sanity check: something changed main ruleset after the qualify check.
                    if ($beatmapset->getRawAttribute('main_ruleset') !== $this->beatmapset->mainRuleset()?->value) {
                        throw new InvariantException('main ruleset has changed.');
                    }

                    $beatmapset->qualify($this->user);
                } else {
                    (new BeatmapsetNominate($this->beatmapset, $this->user))->dispatch();
                }
            });
        }

        $this->beatmapset->refresh();
        $this->beatmapset->refreshCache();
    }

    private function assertValidState(): void
    {
        if (!$this->beatmapset->isPending()) {
            throw new InvariantException(osu_trans('beatmaps.nominations.incorrect_state'));
        }

        if ($this->beatmapset->hype < $this->beatmapset->requiredHype()) {
            throw new InvariantException(osu_trans('beatmaps.nominations.not_enough_hype'));
        }

        // check if there are any outstanding issues still
        if ($this->beatmapset->beatmapDiscussions()->openIssues()->count() > 0) {
            throw new InvariantException(osu_trans('beatmaps.nominations.unresolved_issues'));
        }
    }

    private function nominateRulesets(): array
    {
        $rulesets = $this->beatmapRulesets->intersect($this->nominatedRulesets);
        if ($rulesets->count() === 0) {
            throw new InvariantException(osu_trans('beatmapsets.nominate.hybrid_requires_modes'));
        }

        // Only one ruleset is allowed to have more than one nomination.
        // This needs to be enforced for Beatmapset::mainRuleset()
        $nominationCount = $this->beatmapset->currentNominationCount();

        // add potential counts
        foreach ($rulesets as $ruleset) {
            $nominationCount[$ruleset->legacyName()]++;
        }

        $eligibleRulesets = (new BeatmapsetMainRuleset($this->beatmapset))->eligible();

        $maybeHasMainRuleset = false;
        foreach ($nominationCount as $legacyName => $count) {
            if ($count > 1) {
                if ($maybeHasMainRuleset) {
                    throw new InvariantException(osu_trans('beatmapsets.nominate.too_many_non_main_ruleset'));
                }

                if (!$eligibleRulesets->contains(Ruleset::tryFromName($legacyName))) {
                    throw new InvariantException(osu_trans('beatmapsets.nominate.too_many_ineligible_main_ruleset'));
                }

                $maybeHasMainRuleset = true;
            }
        }

        foreach ($rulesets as $ruleset) {
            $name = $ruleset->legacyName();
            if (!$this->user->isFullBN($name) && !$this->user->isNAT($name)) {
                if (!$this->user->isLimitedBN($name)) {
                    throw new InvariantException(osu_trans('beatmapsets.nominate.incorrect_mode', ['mode' => $name]));
                }

                if ($this->beatmapset->requiresFullBNNomination($name)) {
                    throw new InvariantException(osu_trans('beatmapsets.nominate.full_bn_required'));
                }
            }
        }

        return [
            'comment' => ['modes' => array_map(fn ($ruleset) => $ruleset->legacyName(), $rulesets->toArray())],
            'type' => BeatmapsetEvent::NOMINATE,
            'user_id' => $this->user->getKey(),
        ];
    }

    private function shouldQualify(): bool
    {
        $mainRuleset = $this->beatmapset->mainRuleset();
        if ($mainRuleset === null) {
            return false;
        }

        $nominationsByType = $this->beatmapset->nominationsByType();
        $requiredNominations = $this->beatmapset->requiredNominationCount();

        $modesSatisfied = 0;
        foreach ($requiredNominations as $mode => $count) {
            $fullNominations = static::nominationCount($nominationsByType, 'full', $mode);
            $limitedNominations = static::nominationCount($nominationsByType, 'limited', $mode);
            $totalNominations = $fullNominations + $limitedNominations;

            // Prevent maps with invalid nomination state from going into qualified.
            if (Ruleset::tryFromName($mode) !== $mainRuleset && $limitedNominations > 0) {
                throw new InvariantException(osu_trans('beatmapsets.nominate.invalid_limited_nomination'));
            }

            if ($totalNominations > $count) {
                throw new InvariantException(osu_trans('beatmapsets.nominate.too_many'));
            }

            if ($fullNominations === 0) {
                return false;
            }

            if ($totalNominations === $count) {
                $modesSatisfied++;
            }
        }

        return $modesSatisfied >= $this->beatmapset->playmodeCount();
    }
}
