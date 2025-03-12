<?php

namespace FluxErp\Http\Controllers;

use FluxErp\Actions\FormBuilderForm\CreateFormBuilderForm;
use FluxErp\Actions\FormBuilderForm\DeleteFormBuilderForm;
use FluxErp\Actions\FormBuilderForm\UpdateFormBuilderForm;
use FluxErp\Helpers\ResponseHelper;
use FluxErp\Models\FormBuilderForm;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class FormBuilderFormController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->model = app(FormBuilderForm::class);
    }

    public function create(Request $request): JsonResponse
    {
        $formBuilderForm = CreateFormBuilderForm::make($request->all())
            ->validate()
            ->execute();

        return ResponseHelper::createResponseFromBase(
            statusCode: 201,
            data: $formBuilderForm
        );
    }

    public function delete(string $id): JsonResponse
    {
        try {
            DeleteFormBuilderForm::make(['id' => $id])->validate()->execute();
        } catch (ValidationException $e) {
            return ResponseHelper::createResponseFromBase(
                statusCode: 404,
                data: $e->errors()
            );
        }

        return ResponseHelper::createResponseFromBase(
            statusCode: 204,
            statusMessage: 'form builder form deleted'
        );
    }

    public function update(Request $request): JsonResponse
    {
        $formBuilderForm = UpdateFormBuilderForm::make($request->all())
            ->validate()
            ->execute();

        return ResponseHelper::createResponseFromBase(
            statusCode: 200,
            data: $formBuilderForm
        );
    }
}
