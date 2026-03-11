<?php

declare(strict_types=1);

namespace App\Services;

use CodeIgniter\HTTP\RequestInterface;

abstract class BaseRequestHandler
{
    private const INVALID_JSON_FLAG = '__invalid_json';

    /**
     * @return array<string, mixed>
     */
    protected function extractPayload(RequestInterface $request): array
    {
        $contentType = strtolower($request->getHeaderLine('Content-Type'));

        if (str_contains($contentType, 'application/json')) {
            $rawBody = trim($request->getBody());

            if ($rawBody === '') {
                return [];
            }

            $payload = json_decode($rawBody, true);

            if (json_last_error() !== JSON_ERROR_NONE || ! is_array($payload)) {
                return [self::INVALID_JSON_FLAG => true];
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

    /**
     * @param array<string, mixed> $payload
     */
    protected function hasInvalidJson(array $payload): bool
    {
        return isset($payload[self::INVALID_JSON_FLAG]);
    }

    /**
     * @return array{status: int, body: array<string, mixed>}
     */
    protected function invalidJsonResponse(): array
    {
        return [
            'status' => 400,
            'body' => [
                'message' => 'Некорректный JSON в теле запроса.',
            ],
        ];
    }

    /**
     * @param array<string, string> $errors
     * @return array{status: int, body: array<string, mixed>}
     */
    protected function validationErrorResponse(array $errors): array
    {
        return [
            'status' => 422,
            'body' => [
                'message' => 'Ошибка валидации входных данных.',
                'errors' => $errors,
            ],
        ];
    }

    /**
     * @return array{status: int, body: array<string, mixed>}
     */
    protected function notFoundResponse(string $message): array
    {
        return [
            'status' => 404,
            'body' => [
                'message' => $message,
            ],
        ];
    }

    /**
     * @param array<string, mixed> $body
     * @return array{status: int, body: array<string, mixed>}
     */
    protected function okResponse(array $body): array
    {
        return [
            'status' => 200,
            'body' => $body,
        ];
    }

    /**
     * @param array<string, mixed> $body
     * @return array{status: int, body: array<string, mixed>}
     */
    protected function createdResponse(array $body): array
    {
        return [
            'status' => 201,
            'body' => $body,
        ];
    }
}
