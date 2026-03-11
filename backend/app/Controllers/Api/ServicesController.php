<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Services\ServiceRequestHandler;
use CodeIgniter\HTTP\ResponseInterface;

class ServicesController extends BaseApiController
{
    public function __construct(
        private readonly ServiceRequestHandler $serviceRequestHandler = new ServiceRequestHandler()
    ) {
    }

    public function index(): ResponseInterface
    {
        return $this->respondApi($this->serviceRequestHandler->list($this->request->getGet()));
    }

    public function create(): ResponseInterface
    {
        return $this->respondApi($this->serviceRequestHandler->create($this->request));
    }

    public function update(int $id): ResponseInterface
    {
        return $this->respondApi($this->serviceRequestHandler->update($id, $this->request));
    }

    public function delete(int $id): ResponseInterface
    {
        return $this->respondApi($this->serviceRequestHandler->delete($id));
    }
}
