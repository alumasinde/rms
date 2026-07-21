<?php

declare(strict_types=1);

namespace App\Core\Middleware;

use App\Core\Auth\Auth;
use App\Core\Request\Request;
use App\Core\Response\Response;
use Closure;

final class GuestMiddleware implements MiddlewareInterface
{
    public function __construct(private Auth $auth)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        if ($this->auth->check()) {
            return (new Response())->redirect('/dashboard');
        }

        return $next($request);
    }
}
