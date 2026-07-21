<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Auth\Auth;
use App\Core\Cache\Cache;
use App\Core\Config\Config;
use App\Core\Container\Container;
use App\Core\Database\Database;
use App\Core\Exceptions\Handler;
use App\Core\Logger\Logger;
use App\Core\Request\Request;
use App\Core\Router\Router;
use App\Core\Security\Csrf;
use App\Core\Security\Headers;
use App\Core\Session\Session;
use App\Core\View\View;

$basePath = dirname(__DIR__);

// --- Load .env ---
if (is_file($basePath . '/.env')) {
    foreach (file($basePath . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (str_starts_with(trim($line), '#') || !str_contains($line, '=')) {
            continue;
        }
        [$key, $value] = explode('=', $line, 2);
        $_ENV[trim($key)] = trim($value);
    }
}

date_default_timezone_set('Africa/Nairobi');
Headers::apply();

$container = Container::getInstance();

$config = new Config($basePath . '/config');
$container->instance(Config::class, $config);

$container->singleton(Session::class, fn (Container $c) => new Session($c->make(Config::class)));
$container->singleton(\PDO::class, fn (Container $c) => Database::connection($c->make(Config::class)));
$container->singleton(Auth::class, fn (Container $c) => new Auth($c->make(\PDO::class), $c->make(Session::class)));
$container->singleton(Csrf::class, fn (Container $c) => new Csrf($c->make(Session::class)));
$container->singleton(Cache::class, fn () => new Cache($basePath . '/storage/cache'));
$container->singleton(Logger::class, fn () => new Logger($basePath . '/storage/logs/app.log'));
$container->singleton(View::class, fn (Container $c) => new View(
    $basePath . '/resources/views',
    ['auth' => $c->make(Auth::class), 'csrf' => $c->make(Csrf::class)]
));
$container->singleton(Router::class, fn (Container $c) => new Router($c));

$request = new Request();
$container->instance(Request::class, $request);

$router = $container->make(Router::class);
require $basePath . '/routes/web.php'; // registers routes on $router

try {
    $response = $router->dispatch($request);
} catch (\Throwable $e) {
    $handler = new Handler($container->make(Logger::class), (bool) $config->get('app.debug'));
    $response = $handler->handle($e);
}

$response->send();
