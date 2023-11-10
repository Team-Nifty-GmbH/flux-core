<?php

namespace FluxErp\Http\Controllers;

use FluxErp\Actions\FormBuilderForm\CreateFormBuilderForm;
use FluxErp\Actions\FormBuilderForm\DeleteFormBuilderForm;
use FluxErp\Actions\FormBuilderForm\UpdateFormBuilderForm;
use FluxErp\Helpers\ResponseHelper;
use FluxErp\Http\Requests\CreateFormBuilderFormRequest;
use FluxErp\Http\Requests\UpdateFormBuilderFormRequest;
use FluxErp\Models\FormBuilderForm;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class FormBuilderFormController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->model = new FormBuilderForm();
    }

    public function create(CreateFormBuilderFormRequest $request): JsonResponse
    {
        return ResponseHelper::createResponseFromBase(
            statusCode: 201,
            data: CreateFormBuilderForm::make($request->validated())
                ->execute()
        );
    }

    public function update(UpdateFormBuilderFormRequest $request): JsonResponse
    {
        return ResponseHelper::createResponseFromBase(
            statusCode: 200,
            data: UpdateFormBuilderForm::make($request->validated())
                ->execute()
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
}
