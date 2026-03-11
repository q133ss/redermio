<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\BeneficiaryModel;
use App\Validation\BeneficiaryDataValidator;
use CodeIgniter\Files\File;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Throwable;

class BeneficiaryImportService
{
    public function __construct(
        private readonly BeneficiaryModel $beneficiaryModel = new BeneficiaryModel(),
        private readonly BeneficiaryDataValidator $beneficiaryDataValidator = new BeneficiaryDataValidator(),
        private readonly BeneficiaryImportTemplateService $templateService = new BeneficiaryImportTemplateService()
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function import(?File $file): array
    {
        if ($file === null) {
            return ['errors' => ['file' => 'Нужно передать Excel-файл для импорта.']];
        }

        if (method_exists($file, 'isValid') && ! $file->isValid()) {
            return ['errors' => ['file' => 'Не удалось загрузить Excel-файл.']];
        }

        $extension = mb_strtolower((string) $file->getExtension());

        if ($extension !== 'xlsx') {
            return ['errors' => ['file' => 'Поддерживается только формат .xlsx.']];
        }

        try {
            $spreadsheet = IOFactory::load($file->getRealPath());
        } catch (Throwable) {
            return ['errors' => ['file' => 'Не удалось прочитать Excel-файл.']];
        }

        $sheet = $spreadsheet->getSheet(0);
        $rows = $sheet->toArray(null, true, true, true);

        if ($rows === []) {
            $spreadsheet->disconnectWorksheets();

            return ['errors' => ['file' => 'Excel-файл пустой.']];
        }

        $expectedHeaders = $this->templateService->getExpectedHeaders();
        $headerRow = array_values($rows[1] ?? []);
        $headerRow = array_map(
            static fn (mixed $value): string => is_string($value) ? trim($value) : '',
            array_slice($headerRow, 0, count($expectedHeaders))
        );

        if ($headerRow !== $expectedHeaders) {
            $spreadsheet->disconnectWorksheets();

            return ['errors' => ['file' => 'Структура Excel-файла не соответствует шаблону импорта.']];
        }

        $columnMap = array_keys($this->templateService->getColumnMap());
        $itemsToInsert = [];

        foreach ($rows as $rowNumber => $row) {
            if ($rowNumber === 1) {
                continue;
            }

            $values = array_values($row);
            $values = array_slice($values, 0, count($columnMap));

            if ($this->isEmptyRow($values)) {
                continue;
            }

            $payload = [];

            foreach ($columnMap as $index => $field) {
                $value = $values[$index] ?? null;
                $payload[$field] = is_string($value) ? trim($value) : $value;
            }

            [$validatedData, $errors] = $this->beneficiaryDataValidator->validateForCreate($payload);

            if ($errors !== []) {
                $spreadsheet->disconnectWorksheets();

                return [
                    'errors' => [
                        'row' => sprintf('Ошибка в строке %d.', $rowNumber),
                        'fields' => $errors,
                    ],
                ];
            }

            $itemsToInsert[] = $validatedData;
        }

        $spreadsheet->disconnectWorksheets();

        if ($itemsToInsert === []) {
            return ['errors' => ['file' => 'В Excel-файле нет строк для импорта.']];
        }

        $db = db_connect();
        $db->transStart();

        foreach ($itemsToInsert as $item) {
            if (! $this->beneficiaryModel->insert($item)) {
                $db->transRollback();

                return ['errors' => ['file' => 'Не удалось сохранить данные из Excel-файла.']];
            }
        }

        $db->transComplete();

        if (! $db->transStatus()) {
            return ['errors' => ['file' => 'Не удалось завершить импорт Excel-файла.']];
        }

        return [
            'imported_count' => count($itemsToInsert),
        ];
    }

    /**
     * @param array<int, mixed> $values
     */
    private function isEmptyRow(array $values): bool
    {
        foreach ($values as $value) {
            if (is_string($value) && trim($value) !== '') {
                return false;
            }

            if (! is_string($value) && $value !== null && $value !== '') {
                return false;
            }
        }

        return true;
    }
}
