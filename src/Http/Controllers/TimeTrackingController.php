<?php

namespace FluxErp\Http\Controllers;

use FluxErp\Actions\WorkTime\CreateWorkTime;
use FluxErp\Actions\WorkTime\DeleteWorkTime;
use FluxErp\Actions\WorkTime\UpdateWorkTime;
use FluxErp\Helpers\QueryBuilder;
use FluxErp\Helpers\ResponseHelper;
use FluxErp\Models\WorkTime;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class TimeTrackingController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->model = app(WorkTime::class);
    }

    public function create(Request $request): JsonResponse
    {
        $workTime = CreateWorkTime::make($request->all())
            ->validate()
            ->execute();

        return ResponseHelper::createResponseFromBase(
            statusCode: 201,
            data: $workTime,
            statusMessage: 'work time created'
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
            statusMessage: 'work time deleted'
        );
    }

    public function update(Request $request): JsonResponse
    {
        try {
            $workTime = UpdateWorkTime::make($request->all())
                ->validate()
                ->execute();
        } catch (ValidationException $e) {
            return ResponseHelper::createResponseFromBase(
                statusCode: 422,
                data: $e->errors()
            );
        }

        return ResponseHelper::createResponseFromBase(
            statusCode: 200,
            data: $workTime,
            statusMessage: 'work time updated'
        );
    }

    public function userIndex(Request $request): JsonResponse
    {
        $page = max((int) $request->page, 1);
        $perPage = $request->per_page > 500 || $request->per_page < 1 ? 25 : $request->per_page;

        $query = QueryBuilder::filterModel($this->model, $request);
        $data = $query
            ->where('user_id', $request->user()->id)
            ->paginate(perPage: $perPage, page: $page)
            ->appends($request->query());

        return ResponseHelper::createResponseFromBase(
            statusCode: 200,
            data: $data,
        )->setEncodingOptions(JSON_UNESCAPED_SLASHES);
    }
}
