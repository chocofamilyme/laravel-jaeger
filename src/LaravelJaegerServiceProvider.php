<?php

declare(strict_types=1);

namespace Chocofamilyme\LaravelJaeger;

use Illuminate\Console\Events\CommandFinished;
use Illuminate\Console\Events\CommandStarting;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Foundation\Http\Kernel;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Htpp\Client\Events\ConnectionFailed;
use Illuminate\Htpp\Client\Events\RequestSending;
use Illuminate\Htpp\Client\Events\ResponseReceived;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Jaeger\Config;
use OpenTracing\GlobalTracer;

final class LaravelJaegerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/jaeger.php', 'jaeger'
        );
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/jaeger.php' => $this->app->configPath('jaeger.php'),
        ], 'config');

        $this->app->singleton(Jaeger::class, static function () {
            $config = new Config(
                config('jaeger.config'),
                config('jaeger.service_name'),
            );

            $config->initializeTracer();

            $client = GlobalTracer::get();

            return new Jaeger($client);
        });

        app()->terminating(function () {
            app(Jaeger::class)->finish();
        });

        $this->initHttp();
        $this->initConsole();
        $this->initQuery();
        $this->initJob();
        $this->initClient();
    }

    private function initHttp(): void
    {
        if (config('jaeger.listeners.http.enabled') && false === $this->app->runningInConsole()) {
            $router = $this->app->get('router');
            $router->middleware(
                config('jaeger.listeners.http.handler')
            );

            /** @var Kernel $kernel */
            $kernel = $this->app->get(\Illuminate\Contracts\Http\Kernel::class);
            $kernel->pushMiddleware(
                config('jaeger.listeners.http.handler')
            );
        }
    }

    private function initConsole(): void
    {
        if (config('jaeger.listeners.console.enabled') && $this->app->runningInConsole()) {
            Event::listen(CommandStarting::class, config('jaeger.listeners.console.handler'));
            Event::listen(CommandFinished::class, config('jaeger.listeners.console.handler'));
        }
    }

    private function initQuery(): void
    {
        if (config('jaeger.listeners.query.enabled')) {
            Event::listen(QueryExecuted::class, config('jaeger.listeners.query.handler'));
        }
    }

    private function initJob(): void
    {
        if (config('jaeger.listeners.job.enabled')) {
            Event::listen(JobProcessing::class, config('jaeger.listeners.job.handler'));
            Event::listen(JobProcessed::class, config('jaeger.listeners.job.handler'));
            Event::listen(JobFailed::class, config('jaeger.listeners.job.handler'));
        }
    }

    private function initClient(): void
    {
        if (config('jaeger.listeners.client.enabled')) {
            Event::listen(ConnectionFailed::class, config('jaeger.listeners.client.handler'));
            Event::listen(RequestSending::class, config('jaeger.listeners.client.handler'));
            Event::listen(ResponseReceived::class, config('jaeger.listeners.client.handler'));
        }
    }
}