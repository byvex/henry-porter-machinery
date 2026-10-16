<?php

namespace App\Http\Middleware;

use App\Services\Catalogue\LegacyUrls;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LegacyRedirect
{
    public function handle(Request $request, Closure $next): Response
    {
        if (in_array($request->method(), ['GET', 'HEAD'], true) && ! $request->is('admin*', 'livewire*', 'storage*', 'build*')) {
            $path = parse_url($request->getRequestUri(), PHP_URL_PATH) ?: '/';
            $target = app(LegacyUrls::class)->resolve($path, $request->query());
            if ($target !== null && $target !== $request->getRequestUri() && $target !== $path) {
                return redirect($target, 301);
            }
        }
        $response = $next($request);
        if ($request->is('admin*', 'settings*', 'login', 'register') || $request->query('q') || $request->hasAny(['brands', 'categories', 'condition', 'sort'])) {
            $response->headers->set('X-Robots-Tag', 'noindex, follow');
        }

        return $response;
    }
}
