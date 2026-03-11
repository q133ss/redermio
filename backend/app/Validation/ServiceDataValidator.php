<?php

declare(strict_types=1);

namespace App\Validation;

use App\Models\ServiceModel;

class ServiceDataValidator extends BaseDataValidator
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

        $name = $this->validateRequiredString(
            $payload,
            'name',
            255,
            'Название услуги обязательно для заполнения.',
            'Название услуги не должно быть длиннее 255 символов.',
            $errors
        );

        if ($name !== null) {
            if ($this->serviceNameExists($name, $currentId)) {
                $errors['name'] = 'Услуга с таким названием уже существует.';
            } else {
                $data['name'] = $name;
            }
        }

        $data['description'] = $this->validateOptionalText($payload, 'description', $errors);

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
}
