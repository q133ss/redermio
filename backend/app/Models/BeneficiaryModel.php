<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class BeneficiaryModel extends Model
{
    protected $table            = 'beneficiaries';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    protected $useAutoIncrement = true;
    protected array $casts      = [
        'id' => 'integer',
    ];
    protected $allowedFields    = [
        'type',
        'full_name',
        'short_name',
        'document_number',
        'tax_number',
        'phone',
        'email',
        'address',
        'notes',
    ];
    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';
    protected $allowCallbacks = true;
    protected $beforeInsert   = ['normalizeBeneficiaryData'];
    protected $beforeUpdate   = ['normalizeBeneficiaryData'];

    /**
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>
     */
    protected function normalizeBeneficiaryData(array $data): array
    {
        if (! isset($data['data']) || ! is_array($data['data'])) {
            return $data;
        }

        $stringFields = [
            'type',
            'full_name',
            'short_name',
            'document_number',
            'tax_number',
            'phone',
            'email',
            'address',
            'notes',
        ];

        foreach ($stringFields as $field) {
            if (! array_key_exists($field, $data['data']) || ! is_string($data['data'][$field])) {
                continue;
            }

            $value = trim($data['data'][$field]);

            if ($field === 'type') {
                $data['data'][$field] = mb_strtolower($value);
                continue;
            }

            if ($field === 'email') {
                $value = mb_strtolower($value);
            }

            $data['data'][$field] = $value !== '' ? $value : null;
        }

        return $data;
    }
}
