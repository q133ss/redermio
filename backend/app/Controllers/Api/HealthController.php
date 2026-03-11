<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use CodeIgniter\CodeIgniter;
use CodeIgniter\HTTP\ResponseInterface;

class HealthController extends BaseController
{
    public function index(): ResponseInterface
    {
        return $this->response->setJSON([
            'message'     => 'Это сообщение с бека',
            'status'      => 'ok',
            'timestamp'   => date(DATE_ATOM),
            'ci_version'  => CodeIgniter::CI_VERSION,
        ]);
    }
}
