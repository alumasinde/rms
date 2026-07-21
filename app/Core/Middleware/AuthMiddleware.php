<?php

declare(strict_types=1);

namespace App\Core\Middleware;

use App\Core\Auth\Auth;
use App\Core\Request\Request;
use App\Core\Response\Response;
use Closure;

final class AuthMiddleware implements MiddlewareInterface
{
    public function __construct(private Auth $auth)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        if ($this->auth->guest()) {
            return (new Response())->redirect('/login');
        }

        return $next($request);
    }
}
