<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Services\BeneficiaryRequestHandler;
use CodeIgniter\HTTP\ResponseInterface;

class BeneficiariesController extends BaseApiController
{
    public function __construct(
        private readonly BeneficiaryRequestHandler $beneficiaryRequestHandler = new BeneficiaryRequestHandler()
    ) {
    }

    public function index(): ResponseInterface
    {
        return $this->respondApi($this->beneficiaryRequestHandler->list($this->request->getGet()));
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
