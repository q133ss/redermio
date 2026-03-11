<?php

declare(strict_types=1);

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class DemoBeneficiarySeeder extends Seeder
{
    private const DEMO_NOTE_MARKER = '[seed:demo]';

    /**
     * @var list<array<string, string|null>>
     */
    private array $beneficiaries = [
        [
            'type' => 'individual',
            'full_name' => 'Иван Сергеевич Петров',
            'short_name' => null,
            'document_number' => '4010 123456',
            'tax_number' => '770123456789',
            'phone' => '+7 900 111-22-33',
            'email' => 'ivan.petrov@example.test',
            'address' => 'г. Москва, ул. Лесная, д. 10',
            'notes' => 'Тестовые данные для ручной проверки. Поиск по имени, телефону, email, ИНН и документу. ' . self::DEMO_NOTE_MARKER,
        ],
        [
            'type' => 'individual',
            'full_name' => 'Мария Алексеевна Смирнова',
            'short_name' => null,
            'document_number' => '4509 654321',
            'tax_number' => '781234567890',
            'phone' => '+7 911 555-44-11',
            'email' => 'maria.smirnova@example.test',
            'address' => 'г. Санкт-Петербург, пр. Энгельса, д. 25',
            'notes' => 'Тестовые данные для ручной проверки. ' . self::DEMO_NOTE_MARKER,
        ],
        [
            'type' => 'individual',
            'full_name' => 'Сергей Николаевич Волков',
            'short_name' => null,
            'document_number' => '5208 778899',
            'tax_number' => '540987654321',
            'phone' => '+7 913 700-80-90',
            'email' => 'sergey.volkov@example.test',
            'address' => 'г. Новосибирск, ул. Советская, д. 7',
            'notes' => 'Тестовые данные для ручной проверки. ' . self::DEMO_NOTE_MARKER,
        ],
        [
            'type' => 'individual',
            'full_name' => 'Елена Викторовна Орлова',
            'short_name' => null,
            'document_number' => '6607 110022',
            'tax_number' => '660112233445',
            'phone' => '+7 922 345-67-89',
            'email' => 'elena.orlova@example.test',
            'address' => 'г. Екатеринбург, ул. Малышева, д. 48',
            'notes' => 'Тестовые данные для ручной проверки. ' . self::DEMO_NOTE_MARKER,
        ],
        [
            'type' => 'individual',
            'full_name' => 'Ольга Дмитриевна Кузнецова',
            'short_name' => null,
            'document_number' => '7805 221133',
            'tax_number' => '780556677889',
            'phone' => '+7 921 600-12-34',
            'email' => 'olga.kuznetsova@example.test',
            'address' => 'г. Санкт-Петербург, ул. Типанова, д. 18',
            'notes' => 'Тестовые данные для ручной проверки. ' . self::DEMO_NOTE_MARKER,
        ],
        [
            'type' => 'legal_entity',
            'full_name' => 'ООО Помощь рядом',
            'short_name' => 'Помощь рядом',
            'document_number' => 'ОГРН 1207700123456',
            'tax_number' => '7705123456',
            'phone' => '+7 495 700-10-20',
            'email' => 'office@help-near.test',
            'address' => 'г. Москва, ул. Правды, д. 15',
            'notes' => 'Тестовые данные для ручной проверки. Юридическое лицо. ' . self::DEMO_NOTE_MARKER,
        ],
        [
            'type' => 'legal_entity',
            'full_name' => 'АНО Центр поддержки семьи',
            'short_name' => 'Центр поддержки семьи',
            'document_number' => 'ОГРН 1197800001122',
            'tax_number' => '7804123456',
            'phone' => '+7 812 320-45-67',
            'email' => 'info@family-support.test',
            'address' => 'г. Санкт-Петербург, наб. Обводного канала, д. 52',
            'notes' => 'Тестовые данные для ручной проверки. Юридическое лицо. ' . self::DEMO_NOTE_MARKER,
        ],
        [
            'type' => 'legal_entity',
            'full_name' => 'БФ Открытые возможности',
            'short_name' => 'Открытые возможности',
            'document_number' => 'ОГРН 1186600007788',
            'tax_number' => '6670456123',
            'phone' => '+7 343 290-11-22',
            'email' => 'contact@open-opportunities.test',
            'address' => 'г. Екатеринбург, ул. Белинского, д. 86',
            'notes' => 'Тестовые данные для ручной проверки. Юридическое лицо. ' . self::DEMO_NOTE_MARKER,
        ],
        [
            'type' => 'legal_entity',
            'full_name' => 'Ассоциация Новая опора',
            'short_name' => 'Новая опора',
            'document_number' => 'ОГРН 1225400003344',
            'tax_number' => '5402123456',
            'phone' => '+7 383 210-98-76',
            'email' => 'hello@new-support.test',
            'address' => 'г. Новосибирск, ул. Державина, д. 14',
            'notes' => 'Тестовые данные для ручной проверки. Юридическое лицо. ' . self::DEMO_NOTE_MARKER,
        ],
    ];

    public function run(): void
    {
        $this->db->table('beneficiaries')
            ->like('notes', self::DEMO_NOTE_MARKER)
            ->delete();

        $builder = $this->db->table('beneficiaries');
        $now = date('Y-m-d H:i:s');

        foreach ($this->beneficiaries as $beneficiary) {
            $builder->insert([
                ...$beneficiary,
                'created_at' => $now,
                'updated_at' => $now,
                'deleted_at' => null,
            ]);
        }
    }
}
