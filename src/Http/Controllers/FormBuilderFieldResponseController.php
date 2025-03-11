<?php

namespace FluxErp\Http\Controllers;

use FluxErp\Actions\FormBuilderFieldResponse\CreateFormBuilderFieldResponse;
use FluxErp\Actions\FormBuilderFieldResponse\DeleteFormBuilderFieldResponse;
use FluxErp\Actions\FormBuilderFieldResponse\UpdateFormBuilderFieldResponse;
use FluxErp\Helpers\ResponseHelper;
use FluxErp\Models\FormBuilderFieldResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class FormBuilderFieldResponseController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->model = app(FormBuilderFieldResponse::class);
    }

    public function create(Request $request): JsonResponse
    {
        $formBuilderFieldResponse = CreateFormBuilderFieldResponse::make($request->all())
            ->validate()
            ->execute();

        return ResponseHelper::createResponseFromBase(
            statusCode: 201,
            data: $formBuilderFieldResponse
        );
    }

    public function delete(int $id): JsonResponse
    {
        try {
            DeleteFormBuilderFieldResponse::make(['id' => $id])
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
            statusMessage: 'form builder field response deleted'
        );
    }

    public function update(Request $request): JsonResponse
    {
        $formBuilderFieldResponse = UpdateFormBuilderFieldResponse::make($request->all())
            ->validate()
            ->execute();

        return ResponseHelper::createResponseFromBase(
            statusCode: 200,
            data: $formBuilderFieldResponse
        );
    }
}
