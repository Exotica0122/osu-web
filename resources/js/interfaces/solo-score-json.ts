// Copyright (c) ppy Pty Ltd <contact@ppy.sh>. Licensed under the GNU Affero General Public License v3.0.
// See the LICENCE file in the repository root for full licence text.

import BeatmapExtendedJson from './beatmap-extended-json';
import BeatmapJson from './beatmap-json';
import Rank from './rank';
import Ruleset from './ruleset';
import { ScoreJsonAvailableIncludes, ScoreJsonDefaultIncludes } from './score-json';
import ScoreModJson from './score-mod-json';

export type SoloScoreStatisticsAttribute =
  | 'good'
  | 'great'
  | 'ignore_hit'
  | 'ignore_miss'
  | 'large_bonus'
  | 'large_tick_hit'
  | 'large_tick_miss'
  | 'legacy_combo_increase'
  | 'meh'
  | 'miss'
  | 'ok'
  | 'perfect'
  | 'small_bonus'
  | 'small_tick_hit'
  | 'small_tick_miss';

type SoloScoreJsonDefaultAttributes = {
  accuracy: number;
  beatmap_id: number;
  best_id: number | null;
  build_id: number | null;
  ended_at: string;
  has_replay: boolean;
  id: number;
  is_perfect_combo: boolean;
  legacy_score_id: number | null;
  legacy_total_score: number;
  max_combo: number;
  mods: ScoreModJson[];
  passed: boolean;
  pp: number | null;
  preserve?: boolean;
  processed?: boolean;
  rank: Rank;
  ranked?: boolean;
  ruleset_id: number;
  started_at: string | null;
  statistics: Partial<Record<SoloScoreStatisticsAttribute, number>>;
  total_score: number;
  type: 'solo_score' | `score_best_${Ruleset}` | `score_${Ruleset}`;
  user_id: number;
} & (
  { legacy_perfect: boolean } |
  {
    legacy_perfect: null;
    maximum_statistics: Partial<Record<SoloScoreStatisticsAttribute, number>>;
  }
);

type SoloScoreJson = SoloScoreJsonDefaultAttributes & ScoreJsonDefaultIncludes & Partial<ScoreJsonAvailableIncludes>;

export default SoloScoreJson;

export type SoloScoreJsonForBeatmap = SoloScoreJson & Required<Pick<SoloScoreJson, 'user'>>;

export type SoloScoreJsonForShow = SoloScoreJson
& Required<Pick<SoloScoreJson, 'beatmapset' | 'best_id' | 'rank_global' | 'user'>>
& {
  beatmap: BeatmapExtendedJson & Required<Pick<BeatmapJson, 'user'>>;
};

export type SoloScoreJsonForUser = SoloScoreJson & Required<Pick<SoloScoreJson, 'beatmap' | 'beatmapset'>>;

export function isSoloScoreJsonForUser(score: SoloScoreJson): score is SoloScoreJsonForUser {
  return score.beatmap != null && score.beatmapset != null;
}
