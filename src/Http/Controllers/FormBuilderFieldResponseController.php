<?php

namespace FluxErp\Http\Controllers;

use FluxErp\Actions\FormBuilderFieldResponse\CreateFormBuilderFieldResponse;
use FluxErp\Actions\FormBuilderFieldResponse\DeleteFormBuilderFieldResponse;
use FluxErp\Actions\FormBuilderFieldResponse\UpdateFormBuilderFieldResponse;
use FluxErp\Helpers\ResponseHelper;
use FluxErp\Http\Requests\CreateFormBuilderFieldResponseRequest;
use FluxErp\Http\Requests\UpdateFormBuilderFieldResponseRequest;
use FluxErp\Models\FormBuilderFieldResponse;
use Illuminate\Validation\ValidationException;

class FormBuilderFieldResponseController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->model = new FormBuilderFieldResponse();
    }

    public function create(CreateFormBuilderFieldResponseRequest $request)
    {
        return ResponseHelper::createResponseFromBase(
            statusCode: 201,
            data: CreateFormBuilderFieldResponse::make($request->validated())
                ->execute()
        );
    }

    public function update(UpdateFormBuilderFieldResponseRequest $request)
    {
        return ResponseHelper::createResponseFromBase(
            statusCode: 200,
            data: UpdateFormBuilderFieldResponse::make($request->validated())
                ->execute()
        );
    }

    public function delete(int $id)
    {
        try {
            DeleteFormBuilderFieldResponse::make(['id' => $id])
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
            statusMessage: 'form builder field response deleted'
        );
    }
}
