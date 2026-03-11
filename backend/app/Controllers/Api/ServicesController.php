<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Services\ServiceManager;
use CodeIgniter\HTTP\ResponseInterface;

class ServicesController extends BaseController
{
    public function __construct(
        private readonly ServiceManager $serviceManager = new ServiceManager()
    ) {
    }

    public function index(): ResponseInterface
    {
        $services = $this->serviceManager->getList($this->request->getGet());

        return $this->response->setJSON($services);
    }
}
