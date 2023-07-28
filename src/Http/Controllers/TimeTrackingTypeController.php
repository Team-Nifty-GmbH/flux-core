<?php

namespace FluxErp\Http\Controllers;

use FluxErp\Actions\WorkTimeType\CreateWorkTimeType;
use FluxErp\Actions\WorkTimeType\DeleteWorkTimeType;
use FluxErp\Actions\WorkTimeType\UpdateWorkTimeType;
use FluxErp\Helpers\ResponseHelper;
use FluxErp\Http\Requests\CreateWorkTimeTypeRequest;
use FluxErp\Models\WorkTimeType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class TimeTrackingTypeController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->model = new WorkTimeType();
    }

    public function create(CreateWorkTimeTypeRequest $request): JsonResponse
    {
        $workTimeType = CreateWorkTimeType::make($request->validated())->execute();

        return ResponseHelper::createResponseFromBase(
            statusCode: 201,
            data: $workTimeType,
            statusMessage: __('work time type created')
        );
    }

    public function update(Request $request): JsonResponse
    {
        $data = $request->all();
        if (! array_is_list($data)) {
            $data = [$request->all()];
        }

        $responses = [];
        foreach ($data as $key => $item) {
            try {
                $responses[] = ResponseHelper::createArrayResponse(
                    statusCode: 200,
                    data: $workTimeType = UpdateWorkTimeType::make($item)->validate()->execute(),
                    additions: ['id' => $workTimeType->id]
                );
            } catch (ValidationException $e) {
                $responses[] = ResponseHelper::createArrayResponse(
                    statusCode: 422,
                    data: $e->errors(),
                    additions: [
                        'id' => array_key_exists('id', $item) ? $item['id'] : null,
                    ]
                );

                unset($data[$key]);
            }
        }

        $statusCode = count($responses) === count($data) ? 200 : (count($data) < 1 ? 422 : 207);

        $bulk = count($responses) > 1;

        return ! $bulk ?
            ResponseHelper::createResponseFromArrayResponse(
                array_merge(
                    array_shift($responses),
                    ['statusMessage' => __('work time type updated')]
                )
            ) :
            ResponseHelper::createResponseFromBase(
                statusCode: $statusCode,
                data: $responses,
                statusMessage: $statusCode === 422 ? null : __('work time type(s) updated'),
                bulk: true
            );
    }

    public function delete(string $id): JsonResponse
    {
        try {
            DeleteWorkTimeType::make(['id' => $id])->validate()->execute();
        } catch (ValidationException $e) {
            return ResponseHelper::createResponseFromBase(
                statusCode: array_key_exists('id', $e->errors()) ? 404 : 423,
                data: $e->errors()
            );
        }

        return ResponseHelper::createResponseFromBase(
            statusCode: 204,
            statusMessage: __('work time type deleted')
        );
    }
}
