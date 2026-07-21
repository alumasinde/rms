<?php

declare(strict_types=1);

namespace App\Core\Middleware;

use App\Core\Request\Request;
use App\Core\Response\Response;
use App\Core\Security\Csrf;
use Closure;

final class CsrfMiddleware implements MiddlewareInterface
{
    public function __construct(private Csrf $csrf)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        if (in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            $token = $request->input('_csrf_token') ?? $request->header('X-CSRF-Token');

            if (!$this->csrf->verify($token)) {
                return (new Response())->status(419)->html('Page expired. Please refresh and try again.');
            }
        }

        return $next($request);
    }
}
