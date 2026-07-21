<?php

declare(strict_types=1);

namespace App\Core\Exceptions;

use App\Core\Logger\Logger;
use App\Core\Response\Response;
use Throwable;

final class Handler
{
    public function __construct(private Logger $logger, private bool $debug)
    {
    }

    public function handle(Throwable $e): Response
    {
        $this->logger->error($e->getMessage(), [
            'exception' => get_class($e),
            'file'      => $e->getFile(),
            'line'      => $e->getLine(),
        ]);

        $response = new Response();

        $status = match (true) {
            $e instanceof NotFoundException      => 404,
            $e instanceof AuthorizationException => 403,
            $e instanceof ValidationException    => 422,
            default                               => 500,
        };

        $body = $this->debug
            ? '<pre>' . htmlspecialchars((string) $e, ENT_QUOTES, 'UTF-8') . '</pre>'
            : match ($status) {
                404 => '<h1>404 — Not Found</h1>',
                403 => '<h1>403 — Forbidden</h1>',
                422 => '<h1>422 — Invalid Data</h1>',
                default => '<h1>500 — Something went wrong</h1>',
            };

        return $response->status($status)->html($body);
    }
}
