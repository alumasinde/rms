<?php

declare(strict_types=1);

use App\Core\Middleware\AuthMiddleware;
use App\Core\Request\Request;
use App\Core\Response\Response;
use App\Core\View\View;

/** @var \App\Core\Router\Router $router */

$router->get('/', function (Request $request, array $params, $container) {
    /** @var View $view */
    $view = $container->make(View::class);
    return (new Response())->html($view->render('home', [], 'layouts.guest'));
});

$router->middleware([AuthMiddleware::class])->get('/dashboard', function (Request $request, array $params, $container) {
    /** @var View $view */
    $view = $container->make(View::class);
    return (new Response())->html($view->render('dashboard.index', [], 'layouts.app'));
});
