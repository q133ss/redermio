<?php

declare(strict_types=1);

namespace App\Database\Seeds;

use Faker\Factory;
use Faker\Generator;
use CodeIgniter\Database\Seeder;

class LoadTestBeneficiarySeeder extends Seeder
{
    private const LOAD_NOTE_MARKER = '[seed:load]';
    private const DEFAULT_RECORDS_COUNT = 1000;
    private const BATCH_SIZE = 200;

    public function run(): void
    {
        $this->db->table('beneficiaries')
            ->like('notes', self::LOAD_NOTE_MARKER)
            ->delete();

        /** @var Generator $faker */
        $faker = Factory::create('ru_RU');
        $builder = $this->db->table('beneficiaries');
        $rows = [];
        $now = date('Y-m-d H:i:s');

        for ($index = 1; $index <= self::DEFAULT_RECORDS_COUNT; $index++) {
            $rows[] = $this->makeBeneficiaryRow($faker, $index, $now);

            if (count($rows) < self::BATCH_SIZE) {
                continue;
            }

            $builder->insertBatch($rows);
            $rows = [];
        }

        if ($rows !== []) {
            $builder->insertBatch($rows);
        }
    }

    /**
     * @return array<string, string|null>
     */
    private function makeBeneficiaryRow(Generator $faker, int $index, string $now): array
    {
        $isIndividual = $faker->boolean(80);
        $suffix = str_pad((string) $index, 4, '0', STR_PAD_LEFT);

        if ($isIndividual) {
            $fullName = $faker->lastName() . ' ' . $faker->firstName() . ' ' . $faker->middleName();

            return [
                'type' => 'individual',
                'full_name' => $fullName,
                'short_name' => null,
                'document_number' => sprintf('%04d %06d', $faker->numberBetween(1000, 9999), $faker->numberBetween(100000, 999999)),
                'tax_number' => '77' . str_pad((string) $faker->unique()->numberBetween(1000000000, 9999999999), 10, '0', STR_PAD_LEFT),
                'phone' => '+7 9' . $faker->numberBetween(10, 99) . ' ' . $faker->numberBetween(100, 999) . '-' . $faker->numberBetween(10, 99) . '-' . $faker->numberBetween(10, 99),
                'email' => 'load-individual-' . $suffix . '@example.test',
                'address' => $faker->city() . ', ' . $faker->streetAddress(),
                'notes' => 'Нагрузочные тестовые данные для проверки поиска и пагинации. ' . self::LOAD_NOTE_MARKER,
                'created_at' => $now,
                'updated_at' => $now,
                'deleted_at' => null,
            ];
        }

        $companyName = 'ООО ' . $faker->company();

        return [
            'type' => 'legal_entity',
            'full_name' => $companyName,
            'short_name' => $companyName,
            'document_number' => 'ОГРН ' . (string) $faker->unique()->numberBetween(1000000000000, 9999999999999),
            'tax_number' => str_pad((string) $faker->unique()->numberBetween(1000000000, 9999999999), 10, '0', STR_PAD_LEFT),
            'phone' => '+7 4' . $faker->numberBetween(10, 99) . ' ' . $faker->numberBetween(100, 999) . '-' . $faker->numberBetween(10, 99) . '-' . $faker->numberBetween(10, 99),
            'email' => 'load-legal-' . $suffix . '@example.test',
            'address' => $faker->city() . ', ' . $faker->streetAddress(),
            'notes' => 'Нагрузочные тестовые данные для проверки поиска и пагинации. ' . self::LOAD_NOTE_MARKER,
            'created_at' => $now,
            'updated_at' => $now,
            'deleted_at' => null,
        ];
    }
}
