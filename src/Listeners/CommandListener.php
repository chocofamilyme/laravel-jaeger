<?php

declare(strict_types=1);

namespace Chocofamilyme\LaravelJaeger\Listeners;

use Chocofamilyme\LaravelJaeger\Jaeger;
use Illuminate\Console\Events\CommandFinished;
use Illuminate\Console\Events\CommandStarting;

final class CommandListener
{
    private Jaeger $jaeger;
    private static ?string $operationName = null;

    public function __construct(Jaeger $jaeger)
    {
        $this->jaeger = $jaeger;
    }

    public function handle($event): void
    {
        if ($event instanceof CommandStarting) {
            $command = $event->command ?? $event->input->getArguments()['command'] ?? 'default';

            self::$operationName = "Console command: php artisan $command";

            $this->jaeger->start(self::$operationName, [
                'console.arguments' => json_encode($event->input->getArguments(), JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE),
                'console.options'   => json_encode($event->input->getOptions(), JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE),
            ]);

            return;
        }

        if ($event instanceof CommandFinished) {
            $command = $event->command ?? $event->input->getArguments()['command'] ?? 'default';

            $operationName = self::$operationName ?? "Console command: php artisan $command";

            $this->jaeger->stop($operationName, [
                'console.exit_code' => (string) $event->exitCode,
            ]);

            return;
        }
    }
}