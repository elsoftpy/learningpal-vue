<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ServeSpaOnBrowserNavigation
{
    /**
     * Serve the SPA shell for browser GET navigations, but keep JSON responses for API/XHR calls.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->isMethod('GET')) {
            return $next($request);
        }

        if ($request->expectsJson() || $request->wantsJson() || $request->ajax()) {
            return $next($request);
        }

        $acceptHeader = (string) $request->header('Accept', '');
        if (! str_contains($acceptHeader, 'text/html')) {
            return $next($request);
        }

        return response()->view('app');
    }
}
