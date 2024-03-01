<?php

namespace FluxErp\Http\Controllers;

use FluxErp\Actions\FormBuilderResponse\CreateFormBuilderResponse;
use FluxErp\Actions\FormBuilderResponse\DeleteFormBuilderResponse;
use FluxErp\Helpers\ResponseHelper;
use FluxErp\Models\FormBuilderResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class FormBuilderResponseController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->model = app(FormBuilderResponse::class);
    }

    public function create(Request $request): JsonResponse
    {
        $formBuilderResponse = CreateFormBuilderResponse::make($request->all())
            ->validate()
            ->execute();

        return ResponseHelper::createResponseFromBase(
            statusCode: 201,
            data: $formBuilderResponse
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
