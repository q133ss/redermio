<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateBeneficiaryServicesTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'auto_increment' => true,
            ],
            'beneficiary_id' => [
                'type' => 'BIGINT',
            ],
            'service_id' => [
                'type' => 'BIGINT',
            ],
            'provided_at' => [
                'type' => 'DATE',
            ],
            'comment' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('beneficiary_id');
        $this->forge->addKey('service_id');
        $this->forge->addKey(['beneficiary_id', 'provided_at']);
        $this->forge->addForeignKey('beneficiary_id', 'beneficiaries', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('service_id', 'services', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->createTable('beneficiary_services');
    }

    public function down(): void
    {
        $this->forge->dropTable('beneficiary_services', true);
    }
}
