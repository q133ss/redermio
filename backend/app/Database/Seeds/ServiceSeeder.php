<?php

declare(strict_types=1);

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ServiceSeeder extends Seeder
{
    /**
     * @var list<array{name: string, description: string, is_active: bool}>
     */
    private array $services = [
        [
            'name' => 'Консультация',
            'description' => 'Консультационная помощь',
            'is_active' => true,
        ],
        [
            'name' => 'Обучение',
            'description' => 'Обучающая поддержка',
            'is_active' => true,
        ],
        [
            'name' => 'Материальная помощь',
            'description' => 'Материальная поддержка',
            'is_active' => true,
        ],
    ];

    public function run(): void
    {
        foreach ($this->services as $service) {
            $this->db->query(
                'INSERT INTO services (name, description, is_active, created_at, updated_at, deleted_at)
                 VALUES (?, ?, ?, NOW(), NOW(), NULL)
                 ON CONFLICT (name)
                 DO UPDATE SET
                    description = EXCLUDED.description,
                    is_active = EXCLUDED.is_active,
                    updated_at = NOW(),
                    deleted_at = NULL',
                [
                    $service['name'],
                    $service['description'],
                    $service['is_active'],
                ]
            );
        }
    }
}
