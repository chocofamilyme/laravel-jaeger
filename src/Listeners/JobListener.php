<?php

declare(strict_types=1);

namespace Chocofamilyme\LaravelJaeger\Listeners;

use Chocofamilyme\LaravelJaeger\Jaeger;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;

final class JobListener
{
    private Jaeger $jaeger;
    private static ?string $operationName = null;

    public function __construct(Jaeger $jaeger)
    {
        $this->jaeger = $jaeger;
    }


    public function handle($event): void
    {
        if ($event instanceof JobProcessing) {
            self::$operationName = "Job {$event->job->resolveName()}";

            $this->jaeger->start(self::$operationName, [
                'job.connection_name' => $event->connectionName,
                'job.id' => $event->job->getJobId(),
                'job.queue' => $event->job->getQueue(),
                'job.body' => $event->job->getRawBody(),
            ]);

            return;
        }

        if ($event instanceof JobProcessed || $event instanceof JobFailed) {
            $operationName = self::$operationName ?? "Job {$event->job->resolveName()}";

            $this->jaeger->stop($operationName);

            return;
        }
    }
}