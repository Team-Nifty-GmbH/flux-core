<?php

namespace FluxErp\Helpers;

use FluxErp\Http\Requests\BaseFormRequest;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

class ValidationHelper
{
    /**
     * @param null            $service
     */
    public static function validateBulkData(array &$data,
        BaseFormRequest $formRequest,
        $service = null,
        Model $model = null): array
    {
        $responses = [];

        foreach ($data as $key => $item) {
            $itemValidation = $formRequest->getRules($item);

            $validator = Validator::make($item, $itemValidation);
            ! $model ?: $validator->addModel($model);

            $response = [
                'id' => array_key_exists('id', $item) ? $item['id'] : null,
            ];

            if ($validator->fails()) {
                $responses[] = ResponseHelper::createArrayResponse(
                    statusCode: 422,
                    data: $validator->errors()->toArray(),
                    additions: $response
                );
                unset($data[$key]);

                continue;
            }

            if ($service && method_exists($service, 'validateItem')) {
                $response = $service->validateItem($validator->validated(), $response);
                if ($response) {
                    $responses[] = $response;
                    unset($data[$key]);

                    continue;
                }
            }

            $data[$key] = $validator->validated();
        }

        return $responses;
    }
}
