<?php

// Copyright (c) ppy Pty Ltd <contact@ppy.sh>. Licensed under the GNU Affero General Public License v3.0.
// See the LICENCE file in the repository root for full licence text.

namespace Tests\Singletons;

use App\Exceptions\ContentModerationException;
use App\Models\ChatFilter;
use Tests\TestCase;

class ChatFiltersTest extends TestCase
{
    public static function plainFilterTests()
    {
        return [
            ['bad phrase', 'good phrase'],
            ['WHAT HAPPENS IF I SAY BAD THING IN CAPS', 'WHAT HAPPENS IF I SAY good THING IN CAPS'],
            ['thing is bad', 'thing is good'],
            ['look at this badness', 'look at this goodness'],
        ];
    }

    public static function fullWordFilterTests()
    {
        return [
            ['fullword at the start', 'okay at the start'],
            ['FULLWORD IN CAPS!!', 'okay IN CAPS!!'],
            ['at the end is fullword', 'at the end is okay'],
            ['middle is where the fullword is', 'middle is where the okay is'],
            ['anotherfullword is not replaced', 'anotherfullword is not replaced'],
            ['fullword fullword2', 'okay great'],
            ['fullwordfullword2', 'fullwordfullword2'],
            ['i do a delimiter/inside', 'i do a nice try'],
            ['español', 'español'],
        ];
    }

    public static function blockingFilterTests()
    {
        return [
            ['absolutely forbidden'],
            ['sPoNGeBoB SaYS aBSolUtElY FoRbIdDeN'],
            ['this is absolutely forbidden full stop!!!'],
        ];
    }

    /**
     * @dataProvider plainFilterTests
     */
    public function testPlainFilterReplacement($input, $expectedOutput)
    {
        ChatFilter::factory()->createMany([
            ['match' => 'bad', 'replacement' => 'good'],
            ['match' => 'fullword', 'replacement' => 'okay', 'whitespace_delimited' => true],
            ['match' => 'absolutely forbidden', 'replacement' => '', 'block' => true],
        ]);

        $result = app('chat-filters')->filter($input);
        $this->assertSame($expectedOutput, $result);
    }

    /**
     * @dataProvider fullWordFilterTests
     */
    public function testWhitespaceDelimitedFilterReplacement($input, $expectedOutput)
    {
        ChatFilter::factory()->createMany([
            ['match' => 'bad', 'replacement' => 'good'],
            ['match' => 'fullword', 'replacement' => 'okay', 'whitespace_delimited' => true],
            ['match' => 'fullword2', 'replacement' => 'great', 'whitespace_delimited' => true],
            ['match' => 'delimiter/inside', 'replacement' => 'nice try', 'whitespace_delimited' => true],
            ['match' => 'absolutely forbidden', 'replacement' => '', 'block' => true],
            ['match' => 'ñ', 'replacement' => 'nnnn', 'whitespace_delimited' => true],
        ]);

        $result = app('chat-filters')->filter($input);
        $this->assertSame($expectedOutput, $result);
    }

    /**
     * @dataProvider blockingFilterTests
     */
    public function testBlockingFilter($input)
    {
        ChatFilter::factory()->createMany([
            ['match' => 'bad', 'replacement' => 'good'],
            ['match' => 'fullword', 'replacement' => 'okay', 'whitespace_delimited' => true],
            ['match' => 'absolutely forbidden', 'replacement' => '', 'block' => true],
        ]);

        $this->expectException(ContentModerationException::class);
        app('chat-filters')->filter($input);
    }

    public function testLackOfBlockingFilters()
    {
        ChatFilter::factory()->createMany([
            ['match' => 'bad', 'replacement' => 'good'],
            ['match' => 'fullword', 'replacement' => 'okay', 'whitespace_delimited' => true],
        ]);

        $this->expectNotToPerformAssertions();
        app('chat-filters')->filter('this should be completely fine');
    }
}
