<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

abstract class BaseApiController extends BaseController
{
    /**
     * @param array{status: int, body: array<string, mixed>} $result
     */
    protected function respondApi(array $result): ResponseInterface
    {
        return $this->response
            ->setStatusCode($result['status'])
            ->setJSON($result['body']);
    }
}
