<?php

declare(strict_types=1);

namespace Chocofamilyme\LaravelJaeger\Tests;

use Chocofamilyme\LaravelJaeger\LaravelJaegerServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            LaravelJaegerServiceProvider::class,
        ];
    }
}
