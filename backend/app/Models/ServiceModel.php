<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class ServiceModel extends Model
{
    public const DEFAULT_PAGE_SIZE = 20;

    protected $table            = 'services';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    protected $useAutoIncrement = true;
    protected $casts            = [
        'id' => 'integer',
        'is_active' => 'boolean',
    ];
    protected $allowedFields    = [
        'name',
        'description',
        'is_active',
    ];
    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';
    protected $validationRules = [
        'name' => 'required|string|max_length[255]|is_unique[services.name,id,{id}]',
        'description' => 'permit_empty',
        'is_active' => 'permit_empty',
    ];
    protected $validationMessages = [
        'name' => [
            'required' => 'Название услуги обязательно для заполнения.',
            'max_length' => 'Название услуги не должно быть длиннее 255 символов.',
            'is_unique' => 'Услуга с таким названием уже существует.',
        ],
    ];
    protected $skipValidation = false;
    protected $cleanValidationRules = true;
    protected $allowCallbacks = true;
    protected $beforeInsert   = ['normalizeServiceData'];
    protected $beforeUpdate   = ['normalizeServiceData'];

    public function getActiveList(): array
    {
        return $this->where('is_active', true)
            ->orderBy('name', 'ASC')
            ->findAll();
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>
     */
    protected function normalizeServiceData(array $data): array
    {
        if (! isset($data['data']) || ! is_array($data['data'])) {
            return $data;
        }

        if (array_key_exists('name', $data['data']) && is_string($data['data']['name'])) {
            $data['data']['name'] = trim($data['data']['name']);
        }

        if (array_key_exists('description', $data['data']) && is_string($data['data']['description'])) {
            $data['data']['description'] = trim($data['data']['description']);
            $data['data']['description'] = $data['data']['description'] !== '' ? $data['data']['description'] : null;
        }

        if (array_key_exists('is_active', $data['data'])) {
            $data['data']['is_active'] = filter_var(
                $data['data']['is_active'],
                FILTER_VALIDATE_BOOLEAN,
                FILTER_NULL_ON_FAILURE
            ) ?? false;
        }

        return $data;
    }
}
