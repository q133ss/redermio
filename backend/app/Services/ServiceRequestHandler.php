<?php

declare(strict_types=1);

namespace App\Services;

use CodeIgniter\HTTP\RequestInterface;

class ServiceRequestHandler extends BaseRequestHandler
{
    public function __construct(
        private readonly ServiceManager $serviceManager = new ServiceManager()
    ) {
    }

    /**
     * @param array<string, mixed> $query
     * @return array{status: int, body: array<string, mixed>}
     */
    public function list(array $query): array
    {
        return $this->okResponse($this->serviceManager->getList($query));
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

        $result = $this->serviceManager->create($payload);

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

        $result = $this->serviceManager->update($id, $payload);

        if (isset($result['not_found'])) {
            return $this->notFoundResponse('Услуга не найдена.');
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
        $result = $this->serviceManager->delete($id);

        if (isset($result['not_found'])) {
            return $this->notFoundResponse('Услуга не найдена.');
        }

        if (isset($result['errors'])) {
            return $this->validationErrorResponse($result['errors']);
        }

        return $this->okResponse([
            'message' => 'Услуга удалена.',
        ]);
    }
}
