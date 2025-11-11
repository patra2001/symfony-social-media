<?php

namespace App\Constant;

class PostConstant
{
public static function PostConstant(string $routeName = null)
{
    $extrafield = [
        'sale_mission' => [
            'web_url' => 'https://sales-mission.com',
            'data' => 'Enter your data here',
            'others' => 'Additional information'
        ],
        'app_profile' => [ //route name used as key to get the access  
            [
            'web_url' => 'https://sales-mission1.com',
            'data' => 'Enter your data here',
            'others' => 'Additional information'
            ],
            [
            'web_url' => 'https://sales-mission2.com',
            'data' => 'Enter your data here',
            'others' => 'Additional information'
            ],
            [
            'web_url' => 'https://sales-mission3.com',
            'data' => 'Enter your data here',
            'others' => 'Additional information'
            ],
            
        ]
    ];
    if ($routeName && isset($extrafield[$routeName])) {
        return $extrafield[$routeName];
    }else{
        return $extrafield;
    }
}
}