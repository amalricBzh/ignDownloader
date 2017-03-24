<?php

return [
    'settings' => [
        'displayErrorDetails' => true,
        'addContentLengthHeader' => false,  // Fait par le serveur web
        
        // Monolog
        'logger' => [
            'name' => 'ignLogger',
            'path' => __DIR__ . '/../data/logs/ignDownloader.log',
            'level' => \Monolog\Logger::DEBUG,
        ],
        
        // Renderer
        'renderer' => [
            'templatePath' => __DIR__ . '/../templates/'
        ],
        
        // Divers
        'configFile' => __DIR__ . '/../data/config.json',
        'tmpPath' => __DIR__.'/../data/temp/'
    ]
];
