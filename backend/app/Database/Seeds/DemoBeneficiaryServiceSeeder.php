<?php

declare(strict_types=1);

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class DemoBeneficiaryServiceSeeder extends Seeder
{
    private const DEMO_NOTE_MARKER = '[seed:demo]';
    private const DEMO_COMMENT_MARKER = '[seed:demo-assignment]';

    /**
     * @var list<array{beneficiary_email: string, service_key: string, provided_at: string, comment: string}>
     */
    private array $assignments = [
        [
            'beneficiary_email' => 'ivan.petrov@example.test',
            'service_key' => 'consultation',
            'provided_at' => '2026-02-10',
            'comment' => 'Первичная консультация по обращению. ' . self::DEMO_COMMENT_MARKER,
        ],
        [
            'beneficiary_email' => 'ivan.petrov@example.test',
            'service_key' => 'material_support',
            'provided_at' => '2026-02-15',
            'comment' => 'Выдан продуктовый набор. ' . self::DEMO_COMMENT_MARKER,
        ],
        [
            'beneficiary_email' => 'maria.smirnova@example.test',
            'service_key' => 'education',
            'provided_at' => '2026-01-27',
            'comment' => 'Участие в базовом обучающем курсе. ' . self::DEMO_COMMENT_MARKER,
        ],
        [
            'beneficiary_email' => 'office@help-near.test',
            'service_key' => 'consultation',
            'provided_at' => '2026-02-03',
            'comment' => 'Консультация для представителя организации. ' . self::DEMO_COMMENT_MARKER,
        ],
        [
            'beneficiary_email' => 'info@family-support.test',
            'service_key' => 'education',
            'provided_at' => '2026-02-20',
            'comment' => 'Обучение сотрудников работе с реестром. ' . self::DEMO_COMMENT_MARKER,
        ],
        [
            'beneficiary_email' => 'contact@open-opportunities.test',
            'service_key' => 'material_support',
            'provided_at' => '2026-03-01',
            'comment' => 'Переданы расходные материалы. ' . self::DEMO_COMMENT_MARKER,
        ],
    ];

    public function run(): void
    {
        $this->db->table('beneficiary_services')
            ->like('comment', self::DEMO_COMMENT_MARKER)
            ->delete();

        $beneficiaries = $this->db->table('beneficiaries')
            ->select('id, email')
            ->like('notes', self::DEMO_NOTE_MARKER)
            ->where('deleted_at', null)
            ->get()
            ->getResultArray();

        $services = $this->db->table('services')
            ->select('id')
            ->where('deleted_at', null)
            ->orderBy('id', 'ASC')
            ->get()
            ->getResultArray();

        $beneficiaryIdByEmail = [];

        foreach ($beneficiaries as $beneficiary) {
            $beneficiaryIdByEmail[$beneficiary['email']] = (int) $beneficiary['id'];
        }

        $serviceIdByKey = [
            'consultation' => isset($services[0]['id']) ? (int) $services[0]['id'] : null,
            'education' => isset($services[1]['id']) ? (int) $services[1]['id'] : null,
            'material_support' => isset($services[2]['id']) ? (int) $services[2]['id'] : null,
        ];

        $rows = [];
        $now = date('Y-m-d H:i:s');

        foreach ($this->assignments as $assignment) {
            $beneficiaryId = $beneficiaryIdByEmail[$assignment['beneficiary_email']] ?? null;
            $serviceId = $serviceIdByKey[$assignment['service_key']] ?? null;

            if ($beneficiaryId === null || $serviceId === null) {
                continue;
            }

            $rows[] = [
                'beneficiary_id' => $beneficiaryId,
                'service_id' => $serviceId,
                'provided_at' => $assignment['provided_at'],
                'comment' => $assignment['comment'],
                'created_at' => $now,
            ];
        }

        if ($rows === []) {
            return;
        }

        $this->db->table('beneficiary_services')->insertBatch($rows);
    }
}
