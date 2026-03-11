<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Services\BeneficiaryExportService;
use App\Services\BeneficiaryImportService;
use App\Services\BeneficiaryImportTemplateService;
use App\Services\BeneficiaryRequestHandler;
use CodeIgniter\HTTP\ResponseInterface;

class BeneficiariesController extends BaseApiController
{
    public function __construct(
        private readonly BeneficiaryRequestHandler $beneficiaryRequestHandler = new BeneficiaryRequestHandler(),
        private readonly BeneficiaryExportService $beneficiaryExportService = new BeneficiaryExportService(),
        private readonly BeneficiaryImportTemplateService $beneficiaryImportTemplateService = new BeneficiaryImportTemplateService(),
        private readonly BeneficiaryImportService $beneficiaryImportService = new BeneficiaryImportService()
    ) {
    }

    public function index(): ResponseInterface
    {
        return $this->respondApi($this->beneficiaryRequestHandler->list($this->request->getGet()));
    }

    public function export(): ResponseInterface
    {
        $export = $this->beneficiaryExportService->export($this->request->getGet());

        return $this->response
            ->setHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $export['filename'] . '"')
            ->setBody($export['content']);
    }

    public function importTemplate(): ResponseInterface
    {
        $template = $this->beneficiaryImportTemplateService->buildTemplate();

        return $this->response
            ->setHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $template['filename'] . '"')
            ->setBody($template['content']);
    }

    public function import(): ResponseInterface
    {
        $result = $this->beneficiaryImportService->import($this->request->getFile('file'));

        if (isset($result['errors'])) {
            return $this->response
                ->setStatusCode(422)
                ->setJSON([
                    'message' => 'Ошибка импорта Excel-файла.',
                    'errors' => $result['errors'],
                ]);
        }

        return $this->response
            ->setStatusCode(201)
            ->setJSON([
                'message' => 'Импорт благополучателей выполнен.',
                'imported_count' => $result['imported_count'],
            ]);
    }

    public function show($id = null): ResponseInterface
    {
        return $this->respondApi($this->beneficiaryRequestHandler->show((int) $id));
    }

    public function create(): ResponseInterface
    {
        return $this->respondApi($this->beneficiaryRequestHandler->create($this->request));
    }

    public function update($id = null): ResponseInterface
    {
        return $this->respondApi($this->beneficiaryRequestHandler->update((int) $id, $this->request));
    }

    public function delete($id = null): ResponseInterface
    {
        return $this->respondApi($this->beneficiaryRequestHandler->delete((int) $id));
    }
}
