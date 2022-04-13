# Laravel Jaeger wrapper


## Requirements

- PHP ^8.0
- Laravel ^8.0|^9.0

## Installation

You can install the package via composer:

```bash
composer require chocofamilyme/laravel-jaeger
```

You can publish the config file with:
```bash
php artisan vendor:publish --provider="Chocofamilyme\LaravelJaeger\LaravelJaegerServiceProvider" --tag="config"
```

## Basic Usage

1) You need to inject `\Chocofamilyme\LaravelJaeger\Jaeger` class by DI
2) Start new span by command
```php
    $jaeger->start('Some operation', [
        'tag1' => 'test',
        'tag2' => 'test'
    ]);
```
3) do some stuff
4) (optional) stop span 
```php
    $jaeger->stop('Some operation', [
        'tag3' => 'test',
    ]);

```

All unstopped spans will be automatically stopped when application is terminated

### Controlling the rate of traces

In the configuration file you may modify *JAEGER_SAMPLE_RATE* variable
to configure the rate. The variable accepts values from 0 to 1.

For example, if you set 0.1 then only 10% of all traces is displayed.
Set 1 to output them all.

## Listeners

There are 4 available listeners, they are disabled by default, you can turn on or write your own implementation for this listeners in config file

```php
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
]
```

- Http - Start new span for every http request
- Console - Start new span for every running artisan console commands
- Query - Start new span for every executed database query
- Job - Start new span for every dispatched queue job

## Testing

``` bash
composer test
```

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
