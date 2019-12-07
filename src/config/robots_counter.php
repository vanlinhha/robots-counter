<?php
return [
    //Log channel config to save robots counter log file
    'log_channel_config' => [
        'driver' => 'daily',
        'level' => 'emergency',
        'path' => storage_path('logs/robots.log'),
        'days' => 30,
    ]
];