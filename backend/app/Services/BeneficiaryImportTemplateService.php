<?php

declare(strict_types=1);

namespace App\Services;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class BeneficiaryImportTemplateService
{
    /**
     * @return array<string, string>
     */
    public function getColumnMap(): array
    {
        return [
            'type' => 'Тип благополучателя',
            'full_name' => 'Полное имя',
            'short_name' => 'Краткое имя',
            'phone' => 'Телефон',
            'email' => 'Email',
            'tax_number' => 'ИНН',
            'document_number' => 'Номер документа',
            'address' => 'Адрес',
            'notes' => 'Примечание',
        ];
    }

    /**
     * @return array<int, string>
     */
    public function getExpectedHeaders(): array
    {
        return array_values($this->getColumnMap());
    }

    /**
     * @return array{filename: string, content: string}
     */
    public function buildTemplate(): array
    {
        $spreadsheet = new Spreadsheet();

        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Импорт благополучателей');

        $headers = $this->getExpectedHeaders();
        $column = 'A';

        foreach ($headers as $header) {
            $sheet->setCellValue($column . '1', $header);
            $sheet->getColumnDimension($column)->setAutoSize(true);
            $column++;
        }

        $sheet->freezePane('A2');
        $sheet->getStyle('A1:I1')->getFont()->setBold(true);

        $helpSheet = $spreadsheet->createSheet();
        $helpSheet->setTitle('Справка');
        $helpSheet->setCellValue('A1', 'Формат файла');
        $helpSheet->setCellValue('B1', '.xlsx');
        $helpSheet->setCellValue('A2', 'Обязательные поля');
        $helpSheet->setCellValue('B2', 'Тип благополучателя, Полное имя');
        $helpSheet->setCellValue('A3', 'Допустимые значения поля "Тип благополучателя"');
        $helpSheet->setCellValue('B3', 'individual, legal_entity, Физическое лицо, Юридическое лицо');
        $helpSheet->setCellValue('A4', 'Примечание');
        $helpSheet->setCellValue('B4', 'Первая строка должна содержать только заголовки шаблона. Экспортированный файл тоже можно использовать для обратного импорта.');

        foreach (range('A', 'B') as $helpColumn) {
            $helpSheet->getColumnDimension($helpColumn)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);

        ob_start();
        $writer->save('php://output');
        $content = (string) ob_get_clean();

        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);

        return [
            'filename' => 'beneficiaries-import-template.xlsx',
            'content' => $content,
        ];
    }
}
