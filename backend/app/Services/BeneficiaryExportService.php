<?php

declare(strict_types=1);

namespace App\Services;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class BeneficiaryExportService
{
    public function __construct(
        private readonly BeneficiaryManager $beneficiaryManager = new BeneficiaryManager(),
        private readonly BeneficiaryImportTemplateService $beneficiaryImportTemplateService = new BeneficiaryImportTemplateService()
    ) {
    }

    /**
     * @param array<string, mixed> $filters
     * @return array{filename: string, content: string}
     */
    public function export(array $filters): array
    {
        $items = $this->beneficiaryManager->getExportList($filters);
        $columnMap = $this->beneficiaryImportTemplateService->getColumnMap();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Благополучатели');

        $headers = [
            'A1' => $columnMap['type'],
            'B1' => $columnMap['full_name'],
            'C1' => $columnMap['short_name'],
            'D1' => $columnMap['phone'],
            'E1' => $columnMap['email'],
            'F1' => $columnMap['tax_number'],
            'G1' => $columnMap['document_number'],
            'H1' => $columnMap['address'],
            'I1' => $columnMap['notes'],
            'J1' => 'ID',
            'K1' => 'Создан',
            'L1' => 'Обновлен',
        ];

        foreach ($headers as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }

        $sheet->getStyle('A1:L1')->getFont()->setBold(true);

        $rowIndex = 2;

        foreach ($items as $item) {
            $sheet->setCellValue('A' . $rowIndex, $this->mapTypeLabel((string) $item['type']));
            $sheet->setCellValue('B' . $rowIndex, $item['full_name']);
            $sheet->setCellValue('C' . $rowIndex, $item['short_name']);
            $sheet->setCellValue('D' . $rowIndex, $item['phone']);
            $sheet->setCellValue('E' . $rowIndex, $item['email']);
            $sheet->setCellValue('F' . $rowIndex, $item['tax_number']);
            $sheet->setCellValue('G' . $rowIndex, $item['document_number']);
            $sheet->setCellValue('H' . $rowIndex, $item['address']);
            $sheet->setCellValue('I' . $rowIndex, $item['notes']);
            $sheet->setCellValue('J' . $rowIndex, $item['id']);
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

    private function mapTypeLabel(string $type): string
    {
        return $type === 'individual' ? 'Физическое лицо' : 'Юридическое лицо';
    }
}
