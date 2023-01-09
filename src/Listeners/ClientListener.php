<?php

declare(strict_types=1);

namespace Chocofamilyme\LaravelJaeger\Listeners;

use Chocofamilyme\LaravelJaeger\Jaeger;
use Illuminate\Htpp\Client\Events\ConnectionFailed;
use Illuminate\Htpp\Client\Events\RequestSending;
use Illuminate\Htpp\Client\Events\ResponseReceived;
final class ClientListener
{
    private Jaeger $jaeger;
    private static ?string $operationName = null;

    public function __construct(Jaeger $jaeger)
    {
        $this->jaeger = $jaeger;
    }


    public function handle($event): void
    {
        if ($event instanceof RequestSending) {
            self::$operationName = "Client {$event->request->url()}";

            $this->jaeger->start(self::$operationName, [
                'client.connection_name' => $event->request->url(),
                'client.method' => $event->request->method(),
                'client.body' => $event->request->body(),
            ]);

            return;
        }

        if ($event instanceof ResponseReceived || $event instanceof ConnectionFailed) {
            $operationName = self::$operationName ?? "Client {$event->request->url()}";

            $this->jaeger->stop($operationName);

            return;
        }
    }
}