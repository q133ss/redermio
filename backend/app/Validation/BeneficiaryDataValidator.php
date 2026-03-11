<?php

declare(strict_types=1);

namespace App\Validation;

class BeneficiaryDataValidator extends BaseDataValidator
{
    private const ALLOWED_TYPES = ['individual', 'legal_entity'];

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
    public function validateForUpdate(array $payload): array
    {
        return $this->validate($payload);
    }

    /**
     * @param array<string, mixed> $payload
     * @return array{0: array<string, mixed>, 1: array<string, string>}
     */
    private function validate(array $payload): array
    {
        $errors = [];
        $data = [];

        $type = $payload['type'] ?? null;

        if (! is_string($type)) {
            $errors['type'] = 'Тип благополучателя обязателен для заполнения.';
        } else {
            $type = mb_strtolower(trim($type));

            if (! in_array($type, self::ALLOWED_TYPES, true)) {
                $errors['type'] = 'Тип благополучателя должен быть individual или legal_entity.';
            } else {
                $data['type'] = $type;
            }
        }

        $data['full_name'] = $this->validateRequiredString(
            $payload,
            'full_name',
            255,
            'Поле full_name обязательно для заполнения.',
            'Поле full_name не должно быть длиннее 255 символов.',
            $errors
        );
        $data['short_name'] = $this->validateOptionalString($payload, 'short_name', 255, $errors);
        $data['document_number'] = $this->validateOptionalString($payload, 'document_number', 100, $errors);
        $data['tax_number'] = $this->validateOptionalString($payload, 'tax_number', 50, $errors);
        $data['phone'] = $this->validateOptionalString($payload, 'phone', 50, $errors);
        $data['address'] = $this->validateOptionalText($payload, 'address', $errors);
        $data['notes'] = $this->validateOptionalText($payload, 'notes', $errors);
        $data['email'] = $this->validateOptionalEmail($payload, $errors);

        return [$data, $errors];
    }
}
