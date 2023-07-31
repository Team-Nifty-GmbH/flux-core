<?php

namespace FluxErp\Http\Controllers;

use FluxErp\Actions\WorkTime\CreateWorkTime;
use FluxErp\Actions\WorkTime\DeleteWorkTime;
use FluxErp\Actions\WorkTime\UpdateWorkTime;
use FluxErp\Helpers\ResponseHelper;
use FluxErp\Http\Requests\CreateWorkTimeRequest;
use FluxErp\Http\Requests\UpdateWorkTimeRequest;
use FluxErp\Models\WorkTime;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class TimeTrackingController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->model = new WorkTime();
    }

    public function userIndex(Request $request): JsonResponse
    {
        return ResponseHelper::createResponseFromBase(
            statusCode: 200,
            data: $request->user()->workTimes,
        );
    }

    public function create(CreateWorkTimeRequest $request): JsonResponse
    {
        $workTime = CreateWorkTime::make($request->validated())->execute();

        return ResponseHelper::createResponseFromBase(
            statusCode: 201,
            data: $workTime,
            statusMessage: __('work time created')
        );
    }

    public function update(UpdateWorkTimeRequest $request): JsonResponse
    {
        $workTime = UpdateWorkTime::make($request->validated())->execute();

        return ResponseHelper::createResponseFromBase(
            statusCode: 200,
            data: $workTime,
            statusMessage: __('work time updated')
        );
    }

    public function delete(string $id): JsonResponse
    {
        try {
            DeleteWorkTime::make(['id' => $id])->validate()->execute();
        } catch (ValidationException $e) {
            return ResponseHelper::createResponseFromBase(
                statusCode: array_key_exists('id', $e->errors()) ? 404 : 423,
                data: $e->errors()
            );
        }

        return ResponseHelper::createResponseFromBase(
            statusCode: 204,
            statusMessage: __('work time deleted')
        );
    }
}
