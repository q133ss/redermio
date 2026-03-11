<?php

declare(strict_types=1);

namespace App\Validation;

use DateTimeImmutable;

class BeneficiaryServiceDataValidator extends BaseDataValidator
{
    private const MAX_COMMENT_LENGTH = 1000;

    /**
     * @param array<string, mixed> $payload
     * @return array{0: array<int, array<string, mixed>>, 1: array<string, string>}
     */
    public function validateForCreate(array $payload): array
    {
        $items = $this->normalizeItems($payload);

        if ($items === []) {
            return [
                [],
                ['items' => 'Нужно передать хотя бы одну услугу для назначения.'],
            ];
        }

        $validatedItems = [];
        $errors = [];

        foreach ($items as $index => $item) {
            if (! is_array($item)) {
                $errors[sprintf('items.%d', $index)] = 'Каждый элемент назначения должен быть объектом.';
                continue;
            }

            $serviceId = $this->validatePositiveInt(
                $item,
                'service_id',
                sprintf('items.%d.service_id', $index),
                'Нужно указать услугу для назначения.',
                $errors
            );
            $providedAt = $this->validateProvidedAt($item, $index, $errors);
            $comment = $this->validateComment($item, $index, $errors);

            if ($serviceId === null || $providedAt === null) {
                continue;
            }

            $validatedItems[] = [
                'service_id' => $serviceId,
                'provided_at' => $providedAt,
                'comment' => $comment,
            ];
        }

        return [$validatedItems, $errors];
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<int, array<string, mixed>>
     */
    private function normalizeItems(array $payload): array
    {
        if (array_is_list($payload)) {
            return $payload;
        }

        if (isset($payload['items']) && is_array($payload['items'])) {
            return $payload['items'];
        }

        if ($payload === []) {
            return [];
        }

        return [$payload];
    }

    /**
     * @param array<string, mixed> $item
     * @param array<string, string> $errors
     */
    private function validatePositiveInt(
        array $item,
        string $field,
        string $errorKey,
        string $requiredMessage,
        array &$errors
    ): ?int {
        $value = $item[$field] ?? null;

        if ($value === null || $value === '') {
            $errors[$errorKey] = $requiredMessage;

            return null;
        }

        if (is_int($value)) {
            return $value > 0 ? $value : null;
        }

        if (is_string($value) && ctype_digit($value) && (int) $value > 0) {
            return (int) $value;
        }

        $errors[$errorKey] = 'Поле service_id должно быть положительным числом.';

        return null;
    }

    /**
     * @param array<string, mixed> $item
     * @param array<string, string> $errors
     */
    private function validateProvidedAt(array $item, int $index, array &$errors): ?string
    {
        $value = $item['provided_at'] ?? null;
        $errorKey = sprintf('items.%d.provided_at', $index);

        if (! is_string($value)) {
            $errors[$errorKey] = 'Нужно указать дату оказания услуги.';

            return null;
        }

        $value = trim($value);

        if ($value === '') {
            $errors[$errorKey] = 'Нужно указать дату оказания услуги.';

            return null;
        }

        $date = DateTimeImmutable::createFromFormat('Y-m-d', $value);

        if ($date === false || $date->format('Y-m-d') !== $value) {
            $errors[$errorKey] = 'Дата оказания услуги должна быть в формате YYYY-MM-DD.';

            return null;
        }

        return $value;
    }

    /**
     * @param array<string, mixed> $item
     * @param array<string, string> $errors
     */
    private function validateComment(array $item, int $index, array &$errors): ?string
    {
        $value = $item['comment'] ?? null;
        $errorKey = sprintf('items.%d.comment', $index);

        if ($value !== null && ! is_string($value)) {
            $errors[$errorKey] = 'Комментарий должен быть строкой.';

            return null;
        }

        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        if ($value === '') {
            return null;
        }

        if ($this->stringLength($value) > self::MAX_COMMENT_LENGTH) {
            $errors[$errorKey] = 'Комментарий не должен быть длиннее 1000 символов.';

            return null;
        }

        return $value;
    }
}
