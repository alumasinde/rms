<?php

declare(strict_types=1);

namespace App\Core\Validation;

use App\Core\Exceptions\ValidationException;

/**
 * Lightweight rule-based validator.
 * Usage: (new Validator($data, ['email' => 'required|email']))->validate();
 */
final class Validator
{
    private array $errors = [];

    public function __construct(
        private array $data,
        private array $rules
    ) {
    }

    public function validate(): array
    {
        foreach ($this->rules as $field => $ruleString) {
            $rules = explode('|', $ruleString);
            $value = $this->data[$field] ?? null;

            foreach ($rules as $rule) {
                $this->applyRule($field, $value, $rule);
            }
        }

        if (!empty($this->errors)) {
            throw new ValidationException($this->errors);
        }

        return $this->data;
    }

    private function applyRule(string $field, mixed $value, string $rule): void
    {
        [$name, $param] = array_pad(explode(':', $rule, 2), 2, null);

        $isEmpty = $value === null || $value === '';

        match ($name) {
            'required'  => $isEmpty && $this->fail($field, 'This field is required.'),
            'email'     => !$isEmpty && !filter_var($value, FILTER_VALIDATE_EMAIL) && $this->fail($field, 'Must be a valid email address.'),
            'numeric'   => !$isEmpty && !is_numeric($value) && $this->fail($field, 'Must be numeric.'),
            'min'       => !$isEmpty && strlen((string) $value) < (int) $param && $this->fail($field, "Must be at least {$param} characters."),
            'max'       => !$isEmpty && strlen((string) $value) > (int) $param && $this->fail($field, "Must not exceed {$param} characters."),
            'confirmed' => !$isEmpty && $value !== ($this->data["{$field}_confirmation"] ?? null) && $this->fail($field, 'Confirmation does not match.'),
            'phone'     => !$isEmpty && !preg_match('/^(?:\+254|0)[17]\d{8}$/', (string) $value) && $this->fail($field, 'Must be a valid Kenyan phone number.'),
            default     => null,
        };
    }

    private function fail(string $field, string $message): bool
    {
        $this->errors[$field][] = $message;
        return true;
    }
}
