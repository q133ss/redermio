<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ServiceModel;
use App\Validation\ServiceDataValidator;
use CodeIgniter\Database\BaseBuilder;

class ServiceManager
{
    private const MAX_PAGE_SIZE = 100;

    public function __construct(
        private readonly ServiceModel $serviceModel = new ServiceModel(),
        private readonly ServiceDataValidator $serviceDataValidator = new ServiceDataValidator()
    ) {
    }

    /**
     * @param array<string, mixed> $filters
     *
     * @return array<string, mixed>
     */
    public function getList(array $filters): array
    {
        $page = $this->normalizePositiveInt($filters['page'] ?? null, 1);
        $perPage = $this->normalizePositiveInt(
            $filters['per_page'] ?? null,
            ServiceModel::DEFAULT_PAGE_SIZE
        );
        $perPage = min($perPage, self::MAX_PAGE_SIZE);
        $search = trim((string) ($filters['search'] ?? ''));
        $isActive = $this->normalizeBoolean($filters['is_active'] ?? null);

        $builder = $this->createBaseBuilder();
        $this->applyFilters($builder, $search, $isActive);

        $total = (clone $builder)->countAllResults();
        $lastPage = max((int) ceil($total / $perPage), 1);
        $page = min($page, $lastPage);
        $offset = ($page - 1) * $perPage;

        $items = $builder
            ->select([
                'id',
                'name',
                'description',
                'is_active',
                'created_at',
                'updated_at',
            ])
            ->orderBy('name', 'ASC')
            ->limit($perPage, $offset)
            ->get()
            ->getResultArray();
        $items = array_map($this->mapServiceRow(...), $items);

        return [
            'data' => $items,
            'meta' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'last_page' => $lastPage,
            ],
            'filters' => [
                'search' => $search !== '' ? $search : null,
                'is_active' => $isActive,
            ],
        ];
    }

    /**
     * @param array<string, mixed> $payload
     *
     * @return array<string, mixed>
     */
    public function create(array $payload): array
    {
        [$data, $errors] = $this->serviceDataValidator->validateForCreate($payload);

        if ($errors !== []) {
            return ['errors' => $errors];
        }

        if (! $this->serviceModel->insert($data)) {
            return ['errors' => $this->serviceModel->errors()];
        }

        return [
            'data' => $this->getServiceById((int) $this->serviceModel->getInsertID()),
        ];
    }

    /**
     * @param array<string, mixed> $payload
     *
     * @return array<string, mixed>
     */
    public function update(int $id, array $payload): array
    {
        $service = $this->serviceModel->find($id);

        if ($service === null) {
            return ['not_found' => true];
        }

        [$data, $errors] = $this->serviceDataValidator->validateForUpdate($payload, $id);

        if ($errors !== []) {
            return ['errors' => $errors];
        }

        if (! $this->serviceModel->update($id, $data)) {
            return ['errors' => $this->serviceModel->errors()];
        }

        return [
            'data' => $this->getServiceById($id),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function delete(int $id): array
    {
        $service = $this->serviceModel->find($id);

        if ($service === null) {
            return ['not_found' => true];
        }

        $timestamp = date('Y-m-d H:i:s');
        $updated = $this->serviceModel
            ->builder()
            ->where('id', $id)
            ->where('deleted_at', null)
            ->update([
                'deleted_at' => $timestamp,
                'updated_at' => $timestamp,
            ]);

        if ($updated === false) {
            return [
                'errors' => [
                    'service' => 'Не удалось удалить услугу.',
                ],
            ];
        }

        return ['deleted' => true];
    }

    private function createBaseBuilder(): BaseBuilder
    {
        return $this->serviceModel
            ->builder()
            ->where('deleted_at', null);
    }

    private function applyFilters(BaseBuilder $builder, string $search, ?bool $isActive): void
    {
        if ($search !== '') {
            $builder
                ->groupStart()
                ->like('name', $search, 'both', null, true)
                ->orLike('description', $search, 'both', null, true)
                ->groupEnd();
        }

        if ($isActive !== null) {
            $builder->where('is_active', $isActive);
        }
    }

    private function normalizePositiveInt(mixed $value, int $default): int
    {
        if (! is_scalar($value)) {
            return $default;
        }

        $normalized = filter_var($value, FILTER_VALIDATE_INT);

        if ($normalized === false || $normalized < 1) {
            return $default;
        }

        return $normalized;
    }

    private function normalizeBoolean(mixed $value): ?bool
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_bool($value)) {
            return $value;
        }

        if (! is_scalar($value)) {
            return null;
        }

        $normalized = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

        return $normalized;
    }

    /**
     * @param array<string, mixed> $service
     *
     * @return array<string, mixed>
     */
    private function mapServiceRow(array $service): array
    {
        $service['id'] = (int) $service['id'];
        $service['is_active'] = in_array($service['is_active'], [true, 1, '1', 't', 'true'], true);

        return $service;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function getServiceById(int $id): ?array
    {
        $service = $this->serviceModel->find($id);

        if ($service === null) {
            return null;
        }

        return $this->mapServiceRow($service);
    }

}
