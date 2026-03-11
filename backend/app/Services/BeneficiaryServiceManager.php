<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\BeneficiaryModel;
use App\Models\BeneficiaryServiceModel;
use App\Models\ServiceModel;
use App\Validation\BeneficiaryServiceDataValidator;

class BeneficiaryServiceManager
{
    public function __construct(
        private readonly BeneficiaryServiceModel $beneficiaryServiceModel = new BeneficiaryServiceModel(),
        private readonly BeneficiaryModel $beneficiaryModel = new BeneficiaryModel(),
        private readonly ServiceModel $serviceModel = new ServiceModel(),
        private readonly BeneficiaryServiceDataValidator $validator = new BeneficiaryServiceDataValidator()
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function getList(int $beneficiaryId): array
    {
        if (! $this->beneficiaryExists($beneficiaryId)) {
            return ['beneficiary_not_found' => true];
        }

        return [
            'data' => $this->getAssignmentsForBeneficiary($beneficiaryId),
        ];
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function create(int $beneficiaryId, array $payload): array
    {
        if (! $this->beneficiaryExists($beneficiaryId)) {
            return ['beneficiary_not_found' => true];
        }

        [$items, $errors] = $this->validator->validateForCreate($payload);

        if ($errors !== []) {
            return ['errors' => $errors];
        }

        $serviceErrors = $this->validateServicesAvailability($items);

        if ($serviceErrors !== []) {
            return ['errors' => $serviceErrors];
        }

        $createdIds = [];
        $createdAt = date('Y-m-d H:i:s');

        foreach ($items as $item) {
            $this->beneficiaryServiceModel->insert([
                'beneficiary_id' => $beneficiaryId,
                'service_id' => $item['service_id'],
                'provided_at' => $item['provided_at'],
                'comment' => $item['comment'],
                'created_at' => $createdAt,
            ]);

            $createdIds[] = (int) $this->beneficiaryServiceModel->getInsertID();
        }

        return [
            'data' => $this->getAssignmentsByIds($beneficiaryId, $createdIds),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function delete(int $beneficiaryId, int $assignmentId): array
    {
        if (! $this->beneficiaryExists($beneficiaryId)) {
            return ['beneficiary_not_found' => true];
        }

        $assignment = $this->beneficiaryServiceModel
            ->where('id', $assignmentId)
            ->where('beneficiary_id', $beneficiaryId)
            ->first();

        if ($assignment === null) {
            return ['assignment_not_found' => true];
        }

        $this->beneficiaryServiceModel->delete($assignmentId);

        return ['deleted' => true];
    }

    private function beneficiaryExists(int $beneficiaryId): bool
    {
        return $this->beneficiaryModel->find($beneficiaryId) !== null;
    }

    /**
     * @param array<int, array<string, mixed>> $items
     * @return array<string, string>
     */
    private function validateServicesAvailability(array $items): array
    {
        $serviceIds = array_values(array_unique(array_map(
            static fn (array $item): int => (int) $item['service_id'],
            $items
        )));

        $services = $this->serviceModel
            ->where('is_active', true)
            ->whereIn('id', $serviceIds)
            ->findAll();

        $availableIds = array_map(
            static fn (array $service): int => (int) $service['id'],
            $services
        );

        $availableMap = array_fill_keys($availableIds, true);
        $errors = [];

        foreach ($items as $index => $item) {
            if (! isset($availableMap[(int) $item['service_id']])) {
                $errors[sprintf('items.%d.service_id', $index)] = 'Услуга не найдена или недоступна для назначения.';
            }
        }

        return $errors;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function getAssignmentsForBeneficiary(int $beneficiaryId): array
    {
        $items = $this->beneficiaryServiceModel
            ->select([
                'beneficiary_services.id',
                'beneficiary_services.beneficiary_id',
                'beneficiary_services.service_id',
                'beneficiary_services.provided_at',
                'beneficiary_services.comment',
                'beneficiary_services.created_at',
                'services.name AS service_name',
                'services.description AS service_description',
                'services.is_active AS service_is_active',
            ])
            ->join('services', 'services.id = beneficiary_services.service_id', 'inner')
            ->where('beneficiary_services.beneficiary_id', $beneficiaryId)
            ->orderBy('beneficiary_services.provided_at', 'DESC')
            ->orderBy('beneficiary_services.id', 'DESC')
            ->findAll();

        return array_map($this->mapAssignmentRow(...), $items);
    }

    /**
     * @param array<int, int> $assignmentIds
     * @return array<int, array<string, mixed>>
     */
    private function getAssignmentsByIds(int $beneficiaryId, array $assignmentIds): array
    {
        if ($assignmentIds === []) {
            return [];
        }

        $items = $this->beneficiaryServiceModel
            ->select([
                'beneficiary_services.id',
                'beneficiary_services.beneficiary_id',
                'beneficiary_services.service_id',
                'beneficiary_services.provided_at',
                'beneficiary_services.comment',
                'beneficiary_services.created_at',
                'services.name AS service_name',
                'services.description AS service_description',
                'services.is_active AS service_is_active',
            ])
            ->join('services', 'services.id = beneficiary_services.service_id', 'inner')
            ->where('beneficiary_services.beneficiary_id', $beneficiaryId)
            ->whereIn('beneficiary_services.id', $assignmentIds)
            ->orderBy('beneficiary_services.id', 'ASC')
            ->findAll();

        return array_map($this->mapAssignmentRow(...), $items);
    }

    /**
     * @param array<string, mixed> $row
     * @return array<string, mixed>
     */
    private function mapAssignmentRow(array $row): array
    {
        return [
            'id' => (int) $row['id'],
            'beneficiary_id' => (int) $row['beneficiary_id'],
            'service_id' => (int) $row['service_id'],
            'provided_at' => $row['provided_at'],
            'comment' => $row['comment'],
            'created_at' => $row['created_at'],
            'service' => [
                'id' => (int) $row['service_id'],
                'name' => $row['service_name'],
                'description' => $row['service_description'],
                'is_active' => $this->normalizeDatabaseBoolean($row['service_is_active']),
            ],
        ];
    }

    private function normalizeDatabaseBoolean(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_int($value)) {
            return $value === 1;
        }

        if (! is_string($value)) {
            return false;
        }

        return in_array(strtolower($value), ['1', 'true', 't', 'yes', 'y'], true);
    }
}
