<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Services\BeneficiaryServiceRequestHandler;
use CodeIgniter\HTTP\ResponseInterface;

class BeneficiaryServicesController extends BaseApiController
{
    public function __construct(
        private readonly BeneficiaryServiceRequestHandler $beneficiaryServiceRequestHandler = new BeneficiaryServiceRequestHandler()
    ) {
    }

    public function index($beneficiaryId = null): ResponseInterface
    {
        return $this->respondApi($this->beneficiaryServiceRequestHandler->list((int) $beneficiaryId));
    }

    public function create($beneficiaryId = null): ResponseInterface
    {
        return $this->respondApi($this->beneficiaryServiceRequestHandler->create((int) $beneficiaryId, $this->request));
    }

    public function delete($beneficiaryId = null, $assignmentId = null): ResponseInterface
    {
        return $this->respondApi($this->beneficiaryServiceRequestHandler->delete((int) $beneficiaryId, (int) $assignmentId));
    }
}
