<?php

namespace FluxErp\Http\Controllers;

use FluxErp\Actions\FormBuilderResponse\CreateFormBuilderResponse;
use FluxErp\Actions\FormBuilderResponse\DeleteFormBuilderResponse;
use FluxErp\Actions\FormBuilderResponse\UpdateFormBuilderResponse;
use FluxErp\Http\Requests\CreateFormBuilderResponseRequest;
use FluxErp\Models\FormBuilderResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class FormBuilderResponseController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->model = new FormBuilderResponse();
    }

    public function create(CreateFormBuilderResponseRequest $request)
    {
        return $this->createResponseFromBase(
            statusCode: 201,
            data: CreateFormBuilderResponse::make($request->validated())
                ->execute()
        );
    }

    public function update(Request $request)
    {
        return $this->createResponseFromBase(
            statusCode: 200,
            data: UpdateFormBuilderResponse::make($request->validated())
                ->execute()
        );
    }

    public function delete(int $id)
    {
        try {
            DeleteFormBuilderResponse::make(['id' => $id])
                ->validate()
                ->execute();
        } catch (ValidationException $e) {
            return $this->createArrayResponse(
                statusCode: 404,
                data: $e->errors()
            );
        }

        return $this->createArrayResponse(
            statusCode: 204,
            statusMessage: 'form builder response deleted'
        );
    }
}
