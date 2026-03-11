<?php

declare(strict_types=1);

namespace App\Validation;

abstract class BaseDataValidator
{
    /**
     * @param array<string, mixed> $payload
     * @param array<string, string> $errors
     */
    protected function validateRequiredString(
        array $payload,
        string $field,
        int $maxLength,
        string $requiredMessage,
        string $maxLengthMessage,
        array &$errors
    ): ?string {
        $value = $payload[$field] ?? null;

        if (! is_string($value)) {
            $errors[$field] = $requiredMessage;

            return null;
        }

        $value = trim($value);

        if ($value === '') {
            $errors[$field] = $requiredMessage;

            return null;
        }

        if ($this->stringLength($value) > $maxLength) {
            $errors[$field] = $maxLengthMessage;

            return null;
        }

        return $value;
    }

    /**
     * @param array<string, mixed> $payload
     * @param array<string, string> $errors
     */
    protected function validateOptionalString(array $payload, string $field, int $maxLength, array &$errors): ?string
    {
        $value = $payload[$field] ?? null;

        if ($value !== null && ! is_string($value)) {
            $errors[$field] = sprintf('Поле %s должно быть строкой.', $field);

            return null;
        }

        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        if ($value === '') {
            return null;
        }

        if ($this->stringLength($value) > $maxLength) {
            $errors[$field] = sprintf('Поле %s не должно быть длиннее %d символов.', $field, $maxLength);

            return null;
        }

        return $value;
    }

    /**
     * @param array<string, mixed> $payload
     * @param array<string, string> $errors
     */
    protected function validateOptionalText(array $payload, string $field, array &$errors): ?string
    {
        $value = $payload[$field] ?? null;

        if ($value !== null && ! is_string($value)) {
            $errors[$field] = sprintf('Поле %s должно быть строкой.', $field);

            return null;
        }

        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value !== '' ? $value : null;
    }

    /**
     * @param array<string, mixed> $payload
     * @param array<string, string> $errors
     */
    protected function validateOptionalEmail(array $payload, array &$errors): ?string
    {
        $email = $payload['email'] ?? null;

        if ($email !== null && ! is_string($email)) {
            $errors['email'] = 'Поле email должно быть строкой.';

            return null;
        }

        $email = is_string($email) ? mb_strtolower(trim($email)) : null;

        if ($email === '' || $email === null) {
            return null;
        }

        if ($this->stringLength($email) > 255) {
            $errors['email'] = 'Поле email не должно быть длиннее 255 символов.';

            return null;
        }

        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            $errors['email'] = 'Поле email должно содержать корректный адрес.';

            return null;
        }

        return $email;
    }

    protected function normalizeBooleanInput(mixed $value): ?bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_int($value)) {
            return match ($value) {
                1 => true,
                0 => false,
                default => null,
            };
        }

        if (is_string($value)) {
            $normalized = mb_strtolower(trim($value));

            return match ($normalized) {
                '1', 'true' => true,
                '0', 'false' => false,
                default => null,
            };
        }

        return null;
    }

    protected function stringLength(string $value): int
    {
        return function_exists('mb_strlen') ? mb_strlen($value) : strlen($value);
    }
}
