<?php

declare(strict_types=1);

namespace Chocofamilyme\LaravelJaeger;

use SplStack;
use OpenTracing\Span;
use OpenTracing\Tracer;
use OpenTracing\SpanContext;
use const OpenTracing\Formats\TEXT_MAP;

final class Jaeger
{
    private Tracer $tracer;

    private SplStack $spans;

    private bool $isFinished = false;

    private ?SpanContext $serverContext = null;

    public function __construct(Tracer $tracer)
    {
        $this->tracer = $tracer;
        $this->spans = new \SplStack();
    }

    public function __destruct()
    {
        $this->finish();
    }

    public function tracer(): Tracer
    {
        return $this->tracer;
    }

    public function start(string $operationName, array $tags = []): void
    {
        if ($this->spans->isEmpty()) {
            $span = $this->startSpan($operationName, $this->serverContext);
        } else {
            /** @var Span $parentSpan */
            $parentSpan = $this->spans->top();
            $span       = $this->startSpan($operationName, $parentSpan->getContext());
        }

        if ($tags) {
            foreach ($tags as $key => $value) {
                $span->setTag($key, $value);
            }
        }

        $this->spans->push($span);
    }

    public function stop(string $operationName, array $tags = []): void
    {
        if ($this->spans->isEmpty()) {
            return ;
        }

        foreach ($this->spans as $index => $span) {
            /** @var Span $span */
            if (strcmp($span->getOperationName(), $operationName) === 0) {
                foreach ($tags as $key => $value) {
                    $span->setTag($key, $value);
                }
                $span->finish();
                $this->spans->offsetUnset($index);

                break;
            }
        }
    }

    public function inject(array &$carrier): void
    {
        if ($this->getCurrentSpan() === null) {
            throw new \Exception('Can not inject, there is no available span');
        }

        $this->tracer->inject(
            $this->getCurrentSpan()->getContext(),
            TEXT_MAP,
            $carrier,
        );
    }

    public function getCurrentSpan(): ?Span
    {
        if ($this->spans->isEmpty()) {
            return null;
        }

        return $this->spans->top();
    }

    public function initServerContext(array $carrier = null): ?SpanContext
    {
        $this->isFinished = false;

        if (!$carrier) {
            $context = $this->tracer->extract(TEXT_MAP, $_SERVER);
        } else {
            $context = $this->tracer->extract(TEXT_MAP, $carrier);
        }

        $this->serverContext = $context;

        return $this->serverContext;
    }


    public function finish(): void
    {
        if ($this->isFinished) {
            return;
        }

        try {
            $this->finishSpans();
            $this->tracer->flush();
        } catch (\Throwable $e) {
        }

        $this->isFinished = true;
    }

    private function finishSpans(): void
    {
        while (false === $this->spans->isEmpty()) {
            /** @var Span $span */
            $span = $this->spans->pop();

            $span->finish();
        }
    }

    /**
     * @param string           $operationName
     * @param SpanContext|null $context
     *
     * @return Span
     */
    private function startSpan(string $operationName, SpanContext $context = null): Span
    {
        $options = [];

        if ($context !== null) {
            $options['child_of'] = $context;
        }

        return $this->tracer->startSpan($operationName, $options);
    }
}
