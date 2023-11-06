<?php

namespace FluxErp\Http\Controllers;

use FluxErp\Actions\FormBuilderField\CreateFormBuilderField;
use FluxErp\Actions\FormBuilderField\DeleteFormBuilderField;
use FluxErp\Actions\FormBuilderField\UpdateFormBuilderField;
use FluxErp\Helpers\ResponseHelper;
use FluxErp\Http\Requests\CreateFormBuilderFieldRequest;
use FluxErp\Http\Requests\UpdateFormBuilderFieldRequest;
use FluxErp\Models\FormBuilderField;
use Illuminate\Validation\ValidationException;

class FormBuilderFieldController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->model = new FormBuilderField();
    }

    public function create(CreateFormBuilderFieldRequest $request)
    {
        return ResponseHelper::createResponseFromBase(
            statusCode: 201,
            data: CreateFormBuilderField::make($request->validated())
                ->execute()
        );
    }

    public function update(UpdateFormBuilderFieldRequest $request)
    {
        return ResponseHelper::createResponseFromBase(
            statusCode: 200,
            data: UpdateFormBuilderField::make($request->validated())
                ->execute()
        );
    }

    public function delete(int $id)
    {
        try {
            DeleteFormBuilderField::make(['id' => $id])
                ->validate()
                ->execute();
        } catch (ValidationException $e) {
            return ResponseHelper::createArrayResponse(
                statusCode: 404,
                data: $e->errors()
            );
        }

        return ResponseHelper::createArrayResponse(
            statusCode: 204,
            statusMessage: 'form builder field deleted'
        );
    }
}
