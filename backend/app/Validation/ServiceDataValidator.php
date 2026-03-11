<?php

declare(strict_types=1);

namespace App\Validation;

use App\Models\ServiceModel;

class ServiceDataValidator
{
    public function __construct(
        private readonly ServiceModel $serviceModel = new ServiceModel()
    ) {
    }

    /**
     * @param array<string, mixed> $payload
     * @return array{0: array<string, mixed>, 1: array<string, string>}
     */
    public function validateForCreate(array $payload): array
    {
        return $this->validate($payload);
    }

    /**
     * @param array<string, mixed> $payload
     * @return array{0: array<string, mixed>, 1: array<string, string>}
     */
    public function validateForUpdate(array $payload, int $currentId): array
    {
        return $this->validate($payload, $currentId);
    }

    /**
     * @param array<string, mixed> $payload
     * @return array{0: array<string, mixed>, 1: array<string, string>}
     */
    private function validate(array $payload, ?int $currentId = null): array
    {
        $errors = [];
        $data = [];

        $name = $payload['name'] ?? null;

        if (! is_string($name)) {
            $errors['name'] = 'Название услуги обязательно для заполнения.';
        } else {
            $name = trim($name);

            if ($name === '') {
                $errors['name'] = 'Название услуги обязательно для заполнения.';
            } elseif ($this->stringLength($name) > 255) {
                $errors['name'] = 'Название услуги не должно быть длиннее 255 символов.';
            } elseif ($this->serviceNameExists($name, $currentId)) {
                $errors['name'] = 'Услуга с таким названием уже существует.';
            } else {
                $data['name'] = $name;
            }
        }

        $description = $payload['description'] ?? null;

        if ($description !== null && ! is_string($description)) {
            $errors['description'] = 'Описание услуги должно быть строкой.';
        } else {
            $description = is_string($description) ? trim($description) : null;
            $data['description'] = $description !== '' ? $description : null;
        }

        $normalizedIsActive = $this->normalizeBooleanInput($payload['is_active'] ?? true);

        if ($normalizedIsActive === null) {
            $errors['is_active'] = 'Поле активности услуги должно быть булевым значением.';
        } else {
            $data['is_active'] = $normalizedIsActive;
        }

        return [$data, $errors];
    }

    private function serviceNameExists(string $name, ?int $currentId = null): bool
    {
        $builder = $this->serviceModel
            ->withDeleted()
            ->where('name', $name);

        if ($currentId !== null) {
            $builder->where('id !=', $currentId);
        }

        return $builder->first() !== null;
    }

    private function normalizeBooleanInput(mixed $value): ?bool
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

    private function stringLength(string $value): int
    {
        return function_exists('mb_strlen') ? mb_strlen($value) : strlen($value);
    }
}
