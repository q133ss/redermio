<?php

declare(strict_types=1);

namespace App\Services;

use CodeIgniter\HTTP\RequestInterface;

class BeneficiaryServiceRequestHandler extends BaseRequestHandler
{
    public function __construct(
        private readonly BeneficiaryServiceManager $beneficiaryServiceManager = new BeneficiaryServiceManager()
    ) {
    }

    /**
     * @return array{status: int, body: array<string, mixed>}
     */
    public function list(int $beneficiaryId): array
    {
        $result = $this->beneficiaryServiceManager->getList($beneficiaryId);

        if (isset($result['beneficiary_not_found'])) {
            return $this->notFoundResponse('Благополучатель не найден.');
        }

        return $this->okResponse($result);
    }

    /**
     * @return array{status: int, body: array<string, mixed>}
     */
    public function create(int $beneficiaryId, RequestInterface $request): array
    {
        $payload = $this->extractPayload($request);

        if ($this->hasInvalidJson($payload)) {
            return $this->invalidJsonResponse();
        }

        $result = $this->beneficiaryServiceManager->create($beneficiaryId, $payload);

        if (isset($result['beneficiary_not_found'])) {
            return $this->notFoundResponse('Благополучатель не найден.');
        }

        if (isset($result['errors'])) {
            return $this->validationErrorResponse($result['errors']);
        }

        return $this->createdResponse($result);
    }

    /**
     * @return array{status: int, body: array<string, mixed>}
     */
    public function delete(int $beneficiaryId, int $assignmentId): array
    {
        $result = $this->beneficiaryServiceManager->delete($beneficiaryId, $assignmentId);

        if (isset($result['beneficiary_not_found'])) {
            return $this->notFoundResponse('Благополучатель не найден.');
        }

        if (isset($result['assignment_not_found'])) {
            return $this->notFoundResponse('Привязка услуги не найдена.');
        }

        return $this->okResponse([
            'message' => 'Привязка услуги удалена.',
        ]);
    }
}
