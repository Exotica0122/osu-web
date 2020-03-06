<?php

/**
 *    Copyright (c) ppy Pty Ltd <contact@ppy.sh>.
 *
 *    This file is part of osu!web. osu!web is distributed with the hope of
 *    attracting more community contributions to the core ecosystem of osu!.
 *
 *    osu!web is free software: you can redistribute it and/or modify
 *    it under the terms of the Affero GNU General Public License version 3
 *    as published by the Free Software Foundation.
 *
 *    osu!web is distributed WITHOUT ANY WARRANTY; without even the implied
 *    warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *    See the GNU Affero General Public License for more details.
 *
 *    You should have received a copy of the GNU Affero General Public License
 *    along with osu!web.  If not, see <http://www.gnu.org/licenses/>.
 */

return [
    'landing' => [
        'download' => '',
        'online' => '',
        'peak' => '',
        'players' => '',
        'title' => '',
        'see_more_news' => '',

        'slogan' => [
            'main' => '',
            'sub' => '',
        ],
    ],

    'search' => [
        'advanced_link' => '',
        'button' => '',
        'empty_result' => '',
        'keyword_required' => '',
        'placeholder' => '',
        'title' => '',

        'beatmapset' => [
            'login_required' => '',
            'more' => '',
            'more_simple' => '',
            'title' => '',
        ],

        'forum_post' => [
            'all' => '',
            'link' => '',
            'login_required' => '',
            'more_simple' => '',
            'title' => '',

            'label' => [
                'forum' => '',
                'forum_children' => '',
                'topic_id' => '',
                'username' => '',
            ],
        ],

        'mode' => [
            'all' => '',
            'beatmapset' => '',
            'forum_post' => '',
            'user' => '',
            'wiki_page' => '',
        ],

        'user' => [
            'login_required' => '',
            'more' => '',
            'more_simple' => '',
            'more_hidden' => '',
            'title' => 'Spēlētāji',
        ],

        'wiki_page' => [
            'link' => '',
            'more_simple' => 'Rādīt vairāk wiki meklēšanas rezultātus',
            'title' => 'Wiki',
        ],
    ],

    'download' => [
        'tagline' => "sagatavosim <br> tevi!",
        'action' => 'Lejuplādēt osu!',
        'os' => [
            'windows' => 'priekš Windows',
            'macos' => 'priekš macOS',
            'linux' => 'priekš Linux',
        ],
        'mirror' => 'instalācija',
        'macos-fallback' => 'macOS lietotāji',
        'steps' => [
            'register' => [
                'title' => 'izveido profilu',
                'description' => 'seko norādēm, kad palaidīsi spēli, lai varētu ielogoties vai izveidot jaunu kontu',
            ],
            'download' => [
                'title' => 'lejupielādēt spēli',
                'description' => 'spiediet uz augšējās pogas, lai lejupielādētu instalācijas failu, tad palaidiet to!',
            ],
            'beatmaps' => [
                'title' => 'paņemt bītmapi',
                'description' => [
                    '_' => ':browse lietotāju veidoto bītmapju klāstu un sāciet spēlēt!',
                    'browse' => 'pārlūkot',
                ],
            ],
        ],
        'video-guide' => 'video pamācība',
    ],

    'user' => [
        'title' => 'ziņojumu dēlis',
        'news' => [
            'title' => 'Jaunumi',
            'error' => 'Kļūda lādējot ziņas, mēģiniet atsvaidzināt lapu?...',
        ],
        'header' => [
            'stats' => [
                'friends' => 'Draugi Tiešsaistē',
                'games' => 'Spēles',
                'online' => 'Lietotāji Tiešsaistē',
            ],
        ],
        'beatmaps' => [
            'new' => 'Jaunās Rankotās Bītmapes',
            'popular' => 'Populārās Bītmapes',
            'by_user' => 'pēc :user',
        ],
        'buttons' => [
            'download' => 'Lejupielādēt osu!',
            'support' => 'Atbalstīt osu!',
            'store' => 'osu!veikals',
        ],
    ],

    'support-osu' => [
        'title' => 'Wow!',
        'subtitle' => 'Izskatās, ka tu pavadi jauku laiku! :D',
        'body' => [
            'part-1' => 'Vai tu zināji, ka osu! darbojās bez reklāmām, un balstās tikai uz spēlētājiem, kas atbalsta spēles attīstību un veiktspējas izmaksas?',
            'part-2' => 'Vai tu zināji, ka atblastot osu! tu tiec pie kaudzēm noderīgām funkcijām kā piemēram <strong>bītmapju automātisko lejupielādēšanu</strong>, kas notiek, kad skaties citu spēles un kad spēlē multiplayer?',
        ],
        'find-out-more' => 'Spiediet šeit, lai uzzinātu vairāk!',
        'download-starting' => "Neuztraucieties - jūsu lejupielāde ir jau sākusies priekš tevis ;)",
    ],
];
