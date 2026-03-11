<?php

declare(strict_types=1);

namespace App\Services;

use CodeIgniter\HTTP\RequestInterface;

class ServiceRequestHandler
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
        return [
            'status' => 200,
            'body' => $this->serviceManager->getList($query),
        ];
    }

    /**
     * @return array{status: int, body: array<string, mixed>}
     */
    public function create(RequestInterface $request): array
    {
        $payload = $this->extractPayload($request);

        if (isset($payload['__invalid_json'])) {
            return [
                'status' => 400,
                'body' => [
                    'message' => 'Некорректный JSON в теле запроса.',
                ],
            ];
        }

        $result = $this->serviceManager->create($payload);

        if (isset($result['errors'])) {
            return [
                'status' => 422,
                'body' => [
                    'message' => 'Ошибка валидации входных данных.',
                    'errors' => $result['errors'],
                ],
            ];
        }

        return [
            'status' => 201,
            'body' => $result,
        ];
    }

    /**
     * @return array{status: int, body: array<string, mixed>}
     */
    public function update(int $id, RequestInterface $request): array
    {
        $payload = $this->extractPayload($request);

        if (isset($payload['__invalid_json'])) {
            return [
                'status' => 400,
                'body' => [
                    'message' => 'Некорректный JSON в теле запроса.',
                ],
            ];
        }

        $result = $this->serviceManager->update($id, $payload);

        if (isset($result['not_found'])) {
            return [
                'status' => 404,
                'body' => [
                    'message' => 'Услуга не найдена.',
                ],
            ];
        }

        if (isset($result['errors'])) {
            return [
                'status' => 422,
                'body' => [
                    'message' => 'Ошибка валидации входных данных.',
                    'errors' => $result['errors'],
                ],
            ];
        }

        return [
            'status' => 200,
            'body' => $result,
        ];
    }

    /**
     * @return array{status: int, body: array<string, mixed>}
     */
    public function delete(int $id): array
    {
        $result = $this->serviceManager->delete($id);

        if (isset($result['not_found'])) {
            return [
                'status' => 404,
                'body' => [
                    'message' => 'Услуга не найдена.',
                ],
            ];
        }

        return [
            'status' => 200,
            'body' => [
                'message' => 'Услуга удалена.',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function extractPayload(RequestInterface $request): array
    {
        $contentType = strtolower($request->getHeaderLine('Content-Type'));

        if (str_contains($contentType, 'application/json')) {
            $rawBody = trim($request->getBody());

            if ($rawBody === '') {
                return [];
            }

            $payload = json_decode($rawBody, true);

            if (json_last_error() !== JSON_ERROR_NONE || ! is_array($payload)) {
                return ['__invalid_json' => true];
            }

            return $payload;
        }

        $payload = $request->getRawInput();

        if (is_array($payload) && $payload !== []) {
            return $payload;
        }

        $payload = $request->getPost();

        return is_array($payload) ? $payload : [];
    }
}
