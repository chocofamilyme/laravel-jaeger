<?php

use Jaeger\Config;

return [
    'service_name' => env('JAEGER_SERVICE_NAME', env('APP_NAME', 'Laravel')),

    'config' => [
        'sampler' => [
            'type'  => \Jaeger\SAMPLER_TYPE_PROBABILISTIC,
            'param' => env('JAEGER_SAMPLE_RATE', 0.1),
        ],
        'local_agent' => [
            'reporting_host' => env('JAEGER_HOST', 'jaeger'),
            'reporting_port' => env('JAEGER_PORT', 5775),
        ],
        'dispatch_mode' => Config::ZIPKIN_OVER_COMPACT_UDP,
    ],

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