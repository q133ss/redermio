<?php

declare(strict_types=1);

namespace App\Services;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class BeneficiaryExportService
{
    public function __construct(
        private readonly BeneficiaryManager $beneficiaryManager = new BeneficiaryManager()
    ) {
    }

    /**
     * @param array<string, mixed> $filters
     * @return array{filename: string, content: string}
     */
    public function export(array $filters): array
    {
        $items = $this->beneficiaryManager->getExportList($filters);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Благополучатели');

        $headers = [
            'A1' => 'ID',
            'B1' => 'Тип',
            'C1' => 'Полное имя',
            'D1' => 'Краткое имя',
            'E1' => 'Телефон',
            'F1' => 'Email',
            'G1' => 'ИНН',
            'H1' => 'Номер документа',
            'I1' => 'Адрес',
            'J1' => 'Примечание',
            'K1' => 'Создан',
            'L1' => 'Обновлён',
        ];

        foreach ($headers as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }

        $sheet->getStyle('A1:L1')->getFont()->setBold(true);

        $rowIndex = 2;

        foreach ($items as $item) {
            $sheet->setCellValue('A' . $rowIndex, $item['id']);
            $sheet->setCellValue('B' . $rowIndex, $item['type']);
            $sheet->setCellValue('C' . $rowIndex, $item['full_name']);
            $sheet->setCellValue('D' . $rowIndex, $item['short_name']);
            $sheet->setCellValue('E' . $rowIndex, $item['phone']);
            $sheet->setCellValue('F' . $rowIndex, $item['email']);
            $sheet->setCellValue('G' . $rowIndex, $item['tax_number']);
            $sheet->setCellValue('H' . $rowIndex, $item['document_number']);
            $sheet->setCellValue('I' . $rowIndex, $item['address']);
            $sheet->setCellValue('J' . $rowIndex, $item['notes']);
            $sheet->setCellValue('K' . $rowIndex, $item['created_at']);
            $sheet->setCellValue('L' . $rowIndex, $item['updated_at']);
            $rowIndex++;
        }

        foreach (range('A', 'L') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);

        ob_start();
        $writer->save('php://output');
        $content = (string) ob_get_clean();

        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);

        return [
            'filename' => sprintf('beneficiaries-%s.xlsx', date('Y-m-d-His')),
            'content' => $content,
        ];
    }
}
