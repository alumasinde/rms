<?php

declare(strict_types=1);

namespace App\Core\Router;

use App\Core\Container\Container;
use App\Core\Exceptions\NotFoundException;
use App\Core\Request\Request;
use App\Core\Response\Response;
use Closure;

final class Router
{
    /** @var array<string, array<int, array{pattern: string, handler: mixed, middleware: array}>> */
    private array $routes = [
        'GET' => [], 'POST' => [], 'PUT' => [], 'PATCH' => [], 'DELETE' => [],
    ];

    /** @var array<string> */
    private array $groupMiddleware = [];
    private string $groupPrefix = '';
    private array $pendingMiddleware = [];

    public function __construct(private Container $container)
    {
    }

    public function get(string $uri, mixed $handler): self
    {
        return $this->addRoute('GET', $uri, $handler);
    }

    public function post(string $uri, mixed $handler): self
    {
        return $this->addRoute('POST', $uri, $handler);
    }

    public function put(string $uri, mixed $handler): self
    {
        return $this->addRoute('PUT', $uri, $handler);
    }

    public function patch(string $uri, mixed $handler): self
    {
        return $this->addRoute('PATCH', $uri, $handler);
    }

    public function delete(string $uri, mixed $handler): self
    {
        return $this->addRoute('DELETE', $uri, $handler);
    }

    public function group(array $options, Closure $callback): void
    {
        $previousPrefix = $this->groupPrefix;
        $previousMiddleware = $this->groupMiddleware;

        $this->groupPrefix .= $options['prefix'] ?? '';
        $this->groupMiddleware = array_merge($this->groupMiddleware, $options['middleware'] ?? []);

        $callback($this);

        $this->groupPrefix = $previousPrefix;
        $this->groupMiddleware = $previousMiddleware;
    }

    public function middleware(array $middleware): self
    {
        $this->pendingMiddleware = $middleware;
        return $this;
    }

    private function addRoute(string $method, string $uri, mixed $handler): self
    {
        $uri = $this->groupPrefix . $uri;
        $uri = $uri === '' ? '/' : $uri;

        $this->routes[$method][] = [
            'pattern'    => $this->toPattern($uri),
            'handler'    => $handler,
            'middleware' => array_merge($this->groupMiddleware, $this->pendingMiddleware),
        ];

        $this->pendingMiddleware = [];

        return $this;
    }

    private function toPattern(string $uri): string
    {
        // {id} -> named capture group; {id:\d+} -> custom regex
        $pattern = preg_replace_callback(
            '#\{([a-zA-Z_][a-zA-Z0-9_]*)(:[^}]+)?\}#',
            function (array $m) {
                $regex = isset($m[2]) ? ltrim($m[2], ':') : '[^/]+';
                return "(?P<{$m[1]}>{$regex})";
            },
            $uri
        );

        return '#^' . $pattern . '$#';
    }

    public function dispatch(Request $request): Response
    {
        $method = $request->method();
        $uri = $request->uri();

        foreach ($this->routes[$method] ?? [] as $route) {
            if (preg_match($route['pattern'], $uri, $matches)) {
                $params = array_filter(
                    $matches,
                    fn ($key) => is_string($key),
                    ARRAY_FILTER_USE_KEY
                );

                return $this->runPipeline($route, $request, $params);
            }
        }

        throw new NotFoundException("No route matched {$method} {$uri}");
    }

    private function runPipeline(array $route, Request $request, array $params): Response
    {
        $middlewareStack = $route['middleware'];
        $handler = $route['handler'];

        $core = function (Request $request) use ($handler, $params): Response {
            return $this->callHandler($handler, $request, $params);
        };

        $pipeline = array_reduce(
            array_reverse($middlewareStack),
            function (Closure $next, string $middlewareClass) {
                return function (Request $request) use ($next, $middlewareClass): Response {
                    /** @var \App\Core\Middleware\MiddlewareInterface $middleware */
                    $middleware = $this->container->make($middlewareClass);
                    return $middleware->handle($request, $next);
                };
            },
            $core
        );

        return $pipeline($request);
    }

    private function callHandler(mixed $handler, Request $request, array $params): Response
    {
        if ($handler instanceof Closure) {
            return $handler($request, $params, $this->container);
        }

        if (is_string($handler) && str_contains($handler, '@')) {
            [$class, $method] = explode('@', $handler, 2);
            $controller = $this->container->make($class);
            return $controller->{$method}($request, $params);
        }

        throw new NotFoundException('Invalid route handler.');
    }
}
