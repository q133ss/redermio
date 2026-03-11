<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Services\ServiceRequestHandler;
use CodeIgniter\HTTP\ResponseInterface;

class ServicesController extends BaseController
{
    public function __construct(
        private readonly ServiceRequestHandler $serviceRequestHandler = new ServiceRequestHandler()
    ) {
    }

    public function index(): ResponseInterface
    {
        return $this->respond($this->serviceRequestHandler->list($this->request->getGet()));
    }

    public function create(): ResponseInterface
    {
        return $this->respond($this->serviceRequestHandler->create($this->request));
    }

    public function update(int $id): ResponseInterface
    {
        return $this->respond($this->serviceRequestHandler->update($id, $this->request));
    }

    public function delete(int $id): ResponseInterface
    {
        return $this->respond($this->serviceRequestHandler->delete($id));
    }

    /**
     * @param array{status: int, body: array<string, mixed>} $result
     */
    private function respond(array $result): ResponseInterface
    {
        return $this->response
            ->setStatusCode($result['status'])
            ->setJSON($result['body']);
    }
}
