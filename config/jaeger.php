<?php

return [
    'enabled' => env('JAEGER_ENABLED', false),

    'service_name' => env('JAEGER_SERVICE_NAME', env('APP_NAME', 'Laravel')),

    'host' => env('JAEGER_HOST', 'jaeger'),

    'port' => env('JAEGER_PORT', 6831),

    'sample_rate' => env('JAEGER_SAMPLE_RATE', 0.1),

    'listeners' => [
        'http' => [
            'enabled' => env('JAEGER_HTTP_LISTENER_ENABLED', false),
            'handler' => \Chocofamilyme\LaravelJaeger\JaegerMiddleware::class,
        ],
        'console' => [
            'enabled' => env('JAEGER_CONSOLE_LISTENER_ENABLED', false),
            'handler' => \Chocofamilyme\LaravelJaeger\Listeners\CommandListener::class,
        ],
        'query' => [
            'enabled' => env('JAEGER_QUERY_LISTENER_ENABLED', false),
            'handler' => \Chocofamilyme\LaravelJaeger\Listeners\QueryListener::class,
        ],
        'job' => [
            'enabled' => env('JAEGER_JOB_LISTENER_ENABLED', false),
            'handler' => \Chocofamilyme\LaravelJaeger\Listeners\JobListener::class,
        ],
    ],
];