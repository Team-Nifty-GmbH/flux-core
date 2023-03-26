<?php

namespace FluxErp\Helpers;

use Illuminate\Http\JsonResponse;

class ResponseHelper
{
    public static function createResponseFromBase(int $statusCode,
        mixed $data = [],
        string $statusMessage = null,
        array $additions = null,
        bool $bulk = false): JsonResponse
    {
        $headers = ['Content-Language' => app()->getLocale()];

        if (! $bulk) {
            if ($statusCode >= 200 && $statusCode < 300 && $statusCode !== 204) {
                return response()->json(
                    data: self::handle(
                        data: $data,
                        statusCode: $statusCode,
                        statusMessage: $statusMessage,
                        additions: $additions
                    ),
                    status: $statusCode,
                    headers: $headers
                );
            } elseif ($statusCode >= 400 && $statusCode < 500) {
                return response()->json(
                    data: self::handle(
                        errors: $data,
                        statusCode: $statusCode,
                        statusMessage: $statusMessage,
                        additions: $additions
                    ),
                    status: $statusCode,
                    headers: $headers
                );
            } elseif ($statusCode === 204) {
                return self::noContent();
            } else {
                return response()->json(
                    data: self::handle(
                        statusCode: $statusCode,
                        statusMessage: $statusMessage,
                        additions: $additions
                    ),
                    status: $statusCode,
                    headers: $headers
                );
            }
        } else {
            return response()->json(
                data: self::handleMultiStatus(
                    responses: $data,
                    statusCode: $statusCode,
                    statusMessage: $statusMessage,
                    additions: $additions
                ),
                status: $statusCode,
                headers: $headers
            );
        }
    }

    public static function createResponseFromArrayResponse(array $response): JsonResponse
    {
        $bulk = false;
        if (array_key_exists('responses', $response)) {
            $data = $response['responses'];
            $bulk = count($data) > 1;

            if (! $bulk) {
                unset($response['responses']);
                $response = array_merge($response, $data[0]);
                $data = $response['status'] >= 400 && $response['status'] < 500 ?
                    $response['errors'] : $response['data'];
            }
        } else {
            $data = $response['status'] >= 400 && $response['status'] < 500 ?
                $response['errors'] :
                $response['data'] ?? null;
        }

        return self::createResponseFromBase(
            statusCode: $response['status'],
            data: $data,
            statusMessage: array_key_exists('statusMessage', $response) ? $response['statusMessage'] : null,
            additions: array_diff_key(
                $response,
                array_flip([
                    'status', 'data', 'errors', 'statusMessage', 'responses',
                ])
            ),
            bulk: $bulk
        );
    }

    /**
     * @param mixed $data
     */
    public static function createArrayResponse(int $statusCode,
        mixed $data = null,
        array $additions = [],
        string $statusMessage = null,
        bool $bulk = false): array
    {
        $response = [
            'status' => $statusCode,
        ];

        is_null($statusMessage) ?: $response += ['statusMessage' => $statusMessage];

        if (! $bulk) {
            if ($statusCode >= 400 && $statusCode < 500) {
                $response += [
                    'errors' => $data,
                ];
            } elseif ($statusCode === 204) {
                return $response;
            } else {
                $response += [
                    'data' => $data,
                ];
            }
        } else {
            if (is_countable($data) && count($data) < 2) {
                $response = array_merge($response, $data[0]);
            } else {
                $response += ['responses' => $data];
            }
        }

        return array_merge($additions, $response);
    }

    public static function ok(string $statusMessage, array $data = null): JsonResponse
    {
        return response()->json(
            data: self::handle(
                data: $data,
                statusMessage: $statusMessage
            )
        );
    }

    public static function created(string $statusMessage, array $data = null): JsonResponse
    {
        return response()->json(
            data: self::handle(
                data: $data,
                statusCode: 201,
                statusMessage: $statusMessage
            ), status: 201
        );
    }

    public static function noContent(): JsonResponse
    {
        return response()->json(status: 204);
    }

    public static function multiStatus(string $statusMessage, array $responses, int $statusCode): JsonResponse
    {
        return response()->json(
            data: self::handleMultiStatus(
                responses: $responses,
                statusCode: $statusCode,
                statusMessage: $statusMessage
            ), status: $statusCode
        );
    }

    public static function badRequest(string $statusMessage, array $errors = null): JsonResponse
    {
        return response()->json(
            data: self::handle(
                errors: $errors,
                statusCode: 400,
                statusMessage: $statusMessage
            ), status: 400
        );
    }

    public static function notFound(string $statusMessage, array $errors = null): JsonResponse
    {
        return response()->json(
            data: self::handle(
                errors: $errors,
                statusCode: 404,
                statusMessage: $statusMessage
            ), status: 404
        );
    }

    public static function methodNotAllowed(string $statusMessage, array $errors = null): JsonResponse
    {
        return response()->json(
            data: self::handle(
                errors: $errors,
                statusCode: 405,
                statusMessage: $statusMessage
            ), status: 405
        );
    }

    public static function conflict(string $statusMessage, array $errors = null): JsonResponse
    {
        return response()->json(
            data: self::handle(
                errors: $errors,
                statusCode: 409,
                statusMessage: $statusMessage
            ), status: 409
        );
    }

    public static function unprocessableEntity(string $statusMessage, array $errors = null): JsonResponse
    {
        return response()->json(
            data: self::handle(
                errors: $errors,
                statusCode: 422,
                statusMessage: $statusMessage
            ), status: 422
        );
    }

    public static function locked(string $statusMessage, array $errors = null): JsonResponse
    {
        return response()->json(
            data: self::handle(
                errors: $errors,
                statusCode: 423,
                statusMessage: $statusMessage
            ), status: 423
        );
    }

    public static function failedDependency(string $statusMessage, array $errors = null): JsonResponse
    {
        return response()->json(
            data: self::handle(
                errors: $errors,
                statusCode: 424,
                statusMessage: $statusMessage
            ), status: 424
        );
    }

    /**
     * @param array|null $data
     */
    private static function handle(mixed $data = null,
        array $errors = null,
        int $statusCode = 200,
        string $statusMessage = null,
        array $additions = null): array
    {
        $json = [
            'status' => $statusCode,
        ];

        is_null($statusMessage) ?: $json += ['statusMessage' => $statusMessage];
        is_null($data) ?: $json += ['data' => $data];
        is_null($errors) ?: $json += ['errors' => $errors];
        is_null($additions) ?: $json = array_merge($json, $additions);

        return $json;
    }

    private static function handleMultiStatus(array $responses,
        int $statusCode,
        string $statusMessage = null,
        array $additions = null): array
    {
        $json = [
            'status' => $statusCode,
        ];

        is_null($statusMessage) ?: $json += ['statusMessage' => $statusMessage];
        is_null($additions) ?: $json = array_merge($json, $additions);

        return $json + ['responses' => $responses];
    }
}
