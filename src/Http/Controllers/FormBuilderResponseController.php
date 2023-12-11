<?php

namespace FluxErp\Http\Controllers;

use FluxErp\Actions\FormBuilderResponse\CreateFormBuilderResponse;
use FluxErp\Actions\FormBuilderResponse\DeleteFormBuilderResponse;
use FluxErp\Helpers\ResponseHelper;
use FluxErp\Http\Requests\CreateFormBuilderResponseRequest;
use FluxErp\Models\FormBuilderResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class FormBuilderResponseController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->model = new FormBuilderResponse();
    }

    public function create(CreateFormBuilderResponseRequest $request): JsonResponse
    {
        return ResponseHelper::createResponseFromBase(
            statusCode: 201,
            data: CreateFormBuilderResponse::make($request->validated())
                ->execute()
        );
    }

    public function delete(int $id): JsonResponse
    {
        try {
            DeleteFormBuilderResponse::make(['id' => $id])
                ->validate()
                ->execute();
        } catch (ValidationException $e) {
            return ResponseHelper::createResponseFromBase(
                statusCode: 404,
                data: $e->errors()
            );
        }

        return ResponseHelper::createResponseFromBase(
            statusCode: 204,
            statusMessage: 'form builder response deleted'
        );
    }
}
