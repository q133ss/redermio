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
}
