<?php

declare(strict_types=1);

namespace App\Core\Exceptions;

use RuntimeException;

final class ValidationException extends RuntimeException
{
    public function __construct(private array $errors)
    {
        parent::__construct('The given data was invalid.');
    }

    public function errors(): array
    {
        return $this->errors;
    }
}
