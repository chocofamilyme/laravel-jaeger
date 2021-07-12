<?php

declare(strict_types=1);

namespace Chocofamilyme\LaravelJaeger;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Route;
use Closure;

final class JaegerMiddleware
{
    private Jaeger $jaeger;

    public function __construct(Jaeger $jaeger)
    {
        $this->jaeger = $jaeger;
    }

    /**
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $route = Route::getRoutes()->match($request);
        
        if ($route->isFallback) {
            return $next($request);
        }

        $httpMethod = $request->method();
        $uri = $route->uri();

        $headers = [];

        foreach ($request->headers->all() as $key => $value) {
            $headers[$key] = Arr::first($value);
        }

        $jaeger = $this->jaeger;

        $jaeger->initServerContext($headers);
        $jaeger->start("$httpMethod: /$uri", [
            'http.scheme' => $request->getScheme(),
            'http.ip_address' => $request->ip(),
            'http.host' => $request->getHost(),
            'laravel.version' => app()->version(),
        ]);

        return $next($request);
    }
}
