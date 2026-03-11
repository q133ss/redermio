<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class BeneficiaryServiceModel extends Model
{
    protected $table            = 'beneficiary_services';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $useAutoIncrement = true;
    protected array $casts      = [
        'id' => 'integer',
        'beneficiary_id' => 'integer',
        'service_id' => 'integer',
    ];
    protected $allowedFields    = [
        'beneficiary_id',
        'service_id',
        'provided_at',
        'comment',
        'created_at',
    ];
    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected $allowCallbacks = true;
    protected $beforeInsert   = ['normalizeAssignmentData'];
    protected $beforeUpdate   = ['normalizeAssignmentData'];

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    protected function normalizeAssignmentData(array $data): array
    {
        if (! isset($data['data']) || ! is_array($data['data'])) {
            return $data;
        }

        if (array_key_exists('comment', $data['data']) && is_string($data['data']['comment'])) {
            $comment = trim($data['data']['comment']);
            $data['data']['comment'] = $comment !== '' ? $comment : null;
        }

        if (array_key_exists('provided_at', $data['data']) && is_string($data['data']['provided_at'])) {
            $data['data']['provided_at'] = trim($data['data']['provided_at']);
        }

        return $data;
    }
}
