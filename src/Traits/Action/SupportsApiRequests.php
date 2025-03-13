<?php

namespace FluxErp\Traits\Action;

use Exception;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Symfony\Component\HttpKernel\Exception\HttpException;

trait SupportsApiRequests
{
    public static ?int $successCode = null;

    public function __invoke(Request $request): mixed
    {
        $routeParams = $request->route()->parameters();
        $data = array_merge(
            $request->all(),
            $routeParams
        );
        $isBulk = false;

        if (
            $request->isMethod('DELETE')
            && resolve_static(static::models()[0], 'query')->where($routeParams)->doesntExist()
        ) {
            throw HttpException::fromStatusCode(Response::HTTP_NOT_FOUND);
        }

        if (Arr::isAssoc($data)) {
            $data = [$data];
        } else {
            $isBulk = true;
        }

        $results = [];
        $successful = 0;
        $failed = 0;
        $worstStatusCode = Response::HTTP_OK;

        foreach ($data as $item) {
            $itemResult = [];
            $result = null;

            try {
                $result = static::make($item)
                    ->checkPermission()
                    ->validate()
                    ->execute();

                if ($result instanceof Htmlable) {
                    return $result;
                }

                if (is_bool($result) || is_null($result)) {
                    if (! $isBulk) {
                        return response()->noContent();
                    }

                    $itemResult['data'] = null;
                    $statusCode = Response::HTTP_NO_CONTENT;
                } else {
                    $statusCode = static::$successCode ??
                        (
                            $request->isMethod('POST')
                                ? Response::HTTP_CREATED
                                : Response::HTTP_OK
                        );

                    $itemResult['data'] = $result;
                }

                $itemResult['message'] = 'success';
                $successful++;
            } catch (ValidationException $e) {
                $statusCode = $e->status;
                $itemResult['message'] = 'validation failed';
                $itemResult['errors'] = $e->errors();
                $failed++;
            } catch (UnauthorizedException $e) {
                $statusCode = $e->getStatusCode();
                $itemResult['message'] = 'forbidden';
                $itemResult['errors'] = $e->getMessage();
                $failed++;
            } catch (Exception $e) {
                $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR;
                $itemResult['message'] = 'internal server error';
                $itemResult['errors'] = $e->getMessage();
                $failed++;
            }
            $itemResult['status'] = $statusCode;

            if ($statusCode > $worstStatusCode) {
                $worstStatusCode = $statusCode;
            }

            if ($isBulk && $id = data_get($item, 'id')) {
                $itemResult['id'] = $id;
            }

            $results[] = $itemResult;
        }

        $responseContent = $isBulk
            ? [
                'status' => $failed > 0 ? ($successful > 0 ? 'partial' : 'error') : 'success',
                'data' => [
                    'total' => count($data),
                    'successful' => $successful,
                    'failed' => $failed,
                    'items' => $results,
                ],
            ]
            : $results[0];

        if ($isBulk) {
            $responseCode = $failed > 0 && $successful > 0
                ? Response::HTTP_MULTI_STATUS
                : $worstStatusCode;
        } else {
            $responseCode = $worstStatusCode;
        }

        return response()->json($responseContent, $responseCode);
    }
}
