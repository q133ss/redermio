<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\BeneficiaryModel;
use App\Validation\BeneficiaryDataValidator;
use CodeIgniter\Database\BaseBuilder;

class BeneficiaryManager
{
    private const MAX_PAGE_SIZE = 100;
    private const ALLOWED_TYPES = ['individual', 'legal_entity'];

    public function __construct(
        private readonly BeneficiaryModel $beneficiaryModel = new BeneficiaryModel(),
        private readonly BeneficiaryDataValidator $beneficiaryDataValidator = new BeneficiaryDataValidator()
    ) {
    }

    /**
     * @param array<string, mixed> $filters
     * @return array<string, mixed>
     */
    public function getList(array $filters): array
    {
        $page = $this->normalizePositiveInt($filters['page'] ?? null, 1);
        $perPage = $this->normalizePositiveInt($filters['per_page'] ?? null, 20);
        $perPage = min($perPage, self::MAX_PAGE_SIZE);
        $search = trim((string) ($filters['search'] ?? ''));
        $type = $this->normalizeTypeFilter($filters['type'] ?? null);

        $builder = $this->createBaseBuilder();
        $this->applyFilters($builder, $search, $type);

        $total = (clone $builder)->countAllResults();
        $lastPage = max((int) ceil($total / $perPage), 1);
        $page = min($page, $lastPage);
        $offset = ($page - 1) * $perPage;

        $items = $builder
            ->select([
                'id',
                'type',
                'full_name',
                'short_name',
                'document_number',
                'tax_number',
                'phone',
                'email',
                'address',
                'notes',
                'created_at',
                'updated_at',
            ])
            ->orderBy('full_name', 'ASC')
            ->limit($perPage, $offset)
            ->get()
            ->getResultArray();
        $items = array_map($this->mapBeneficiaryRow(...), $items);

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
                'type' => $type,
            ],
        ];
    }

    /**
     * @param array<string, mixed> $filters
     * @return array<int, array<string, mixed>>
     */
    public function getExportList(array $filters): array
    {
        $search = trim((string) ($filters['search'] ?? ''));
        $type = $this->normalizeTypeFilter($filters['type'] ?? null);

        $builder = $this->createBaseBuilder();
        $this->applyFilters($builder, $search, $type);

        $items = $builder
            ->select([
                'id',
                'type',
                'full_name',
                'short_name',
                'document_number',
                'tax_number',
                'phone',
                'email',
                'address',
                'notes',
                'created_at',
                'updated_at',
            ])
            ->orderBy('full_name', 'ASC')
            ->get()
            ->getResultArray();

        return array_map($this->mapBeneficiaryRow(...), $items);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getById(int $id): ?array
    {
        $beneficiary = $this->beneficiaryModel->find($id);

        if ($beneficiary === null) {
            return null;
        }

        return $this->mapBeneficiaryRow($beneficiary);
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function create(array $payload): array
    {
        [$data, $errors] = $this->beneficiaryDataValidator->validateForCreate($payload);

        if ($errors !== []) {
            return ['errors' => $errors];
        }

        if (! $this->beneficiaryModel->insert($data)) {
            return ['errors' => $this->beneficiaryModel->errors()];
        }

        return [
            'data' => $this->getById((int) $this->beneficiaryModel->getInsertID()),
        ];
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function update(int $id, array $payload): array
    {
        $beneficiary = $this->beneficiaryModel->find($id);

        if ($beneficiary === null) {
            return ['not_found' => true];
        }

        [$data, $errors] = $this->beneficiaryDataValidator->validateForUpdate($payload);

        if ($errors !== []) {
            return ['errors' => $errors];
        }

        if (! $this->beneficiaryModel->update($id, $data)) {
            return ['errors' => $this->beneficiaryModel->errors()];
        }

        return [
            'data' => $this->getById($id),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function delete(int $id): array
    {
        $beneficiary = $this->beneficiaryModel->find($id);

        if ($beneficiary === null) {
            return ['not_found' => true];
        }

        $this->beneficiaryModel->delete($id);

        return ['deleted' => true];
    }

    private function createBaseBuilder(): BaseBuilder
    {
        return $this->beneficiaryModel
            ->builder()
            ->where('deleted_at', null);
    }

    private function applyFilters(BaseBuilder $builder, string $search, ?string $type): void
    {
        if ($search !== '') {
            $builder
                ->groupStart()
                ->like('full_name', $search, 'both', null, true)
                ->orLike('short_name', $search, 'both', null, true)
                ->orLike('phone', $search, 'both', null, true)
                ->orLike('email', $search, 'both', null, true)
                ->orLike('tax_number', $search, 'both', null, true)
                ->orLike('document_number', $search, 'both', null, true)
                ->groupEnd();
        }

        if ($type !== null) {
            $builder->where('type', $type);
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

    private function normalizeTypeFilter(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = mb_strtolower(trim($value));

        return in_array($value, self::ALLOWED_TYPES, true) ? $value : null;
    }

    /**
     * @param array<string, mixed> $beneficiary
     * @return array<string, mixed>
     */
    private function mapBeneficiaryRow(array $beneficiary): array
    {
        $beneficiary['id'] = (int) $beneficiary['id'];

        return $beneficiary;
    }
}
