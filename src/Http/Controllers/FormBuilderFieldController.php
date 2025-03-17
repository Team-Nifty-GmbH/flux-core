<?php

namespace FluxErp\Http\Controllers;

use FluxErp\Actions\FormBuilderField\CreateFormBuilderField;
use FluxErp\Actions\FormBuilderField\DeleteFormBuilderField;
use FluxErp\Actions\FormBuilderField\UpdateFormBuilderField;
use FluxErp\Helpers\ResponseHelper;
use FluxErp\Models\FormBuilderField;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class FormBuilderFieldController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->model = app(FormBuilderField::class);
    }

    public function create(Request $request): JsonResponse
    {
        $formBuilderField = CreateFormBuilderField::make($request->all())
            ->validate()
            ->execute();

        return ResponseHelper::createResponseFromBase(
            statusCode: 201,
            data: $formBuilderField
        );
    }

    public function delete(int $id): JsonResponse
    {
        try {
            DeleteFormBuilderField::make(['id' => $id])
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
            statusMessage: 'form builder field deleted'
        );
    }

    public function update(Request $request): JsonResponse
    {
        $formBuilderField = UpdateFormBuilderField::make($request->all())
            ->validate()
            ->execute();

        return ResponseHelper::createResponseFromBase(
            statusCode: 200,
            data: $formBuilderField
        );
    }
}
