<?php

declare(strict_types=1);

namespace App\Services;

use CodeIgniter\HTTP\RequestInterface;

class BeneficiaryRequestHandler extends BaseRequestHandler
{
    public function __construct(
        private readonly BeneficiaryManager $beneficiaryManager = new BeneficiaryManager()
    ) {
    }

    /**
     * @param array<string, mixed> $query
     * @return array{status: int, body: array<string, mixed>}
     */
    public function list(array $query): array
    {
        return $this->okResponse($this->beneficiaryManager->getList($query));
    }

    /**
     * @return array{status: int, body: array<string, mixed>}
     */
    public function show(int $id): array
    {
        $beneficiary = $this->beneficiaryManager->getById($id);

        if ($beneficiary === null) {
            return $this->notFoundResponse('Благополучатель не найден.');
        }

        return $this->okResponse([
            'data' => $beneficiary,
        ]);
    }

    /**
     * @return array{status: int, body: array<string, mixed>}
     */
    public function create(RequestInterface $request): array
    {
        $payload = $this->extractPayload($request);

        if ($this->hasInvalidJson($payload)) {
            return $this->invalidJsonResponse();
        }

        $result = $this->beneficiaryManager->create($payload);

        if (isset($result['errors'])) {
            return $this->validationErrorResponse($result['errors']);
        }

        return $this->createdResponse($result);
    }

    /**
     * @return array{status: int, body: array<string, mixed>}
     */
    public function update(int $id, RequestInterface $request): array
    {
        $payload = $this->extractPayload($request);

        if ($this->hasInvalidJson($payload)) {
            return $this->invalidJsonResponse();
        }

        $result = $this->beneficiaryManager->update($id, $payload);

        if (isset($result['not_found'])) {
            return $this->notFoundResponse('Благополучатель не найден.');
        }

        if (isset($result['errors'])) {
            return $this->validationErrorResponse($result['errors']);
        }

        return $this->okResponse($result);
    }

    /**
     * @return array{status: int, body: array<string, mixed>}
     */
    public function delete(int $id): array
    {
        $result = $this->beneficiaryManager->delete($id);

        if (isset($result['not_found'])) {
            return $this->notFoundResponse('Благополучатель не найден.');
        }

        return $this->okResponse([
            'message' => 'Благополучатель удалён.',
        ]);
    }
}
