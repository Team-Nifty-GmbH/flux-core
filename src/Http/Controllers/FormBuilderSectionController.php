<?php

namespace FluxErp\Http\Controllers;

use FluxErp\Actions\FormBuilderSection\CreateFormBuilderSection;
use FluxErp\Actions\FormBuilderSection\DeleteFormBuilderSection;
use FluxErp\Actions\FormBuilderSection\UpdateFormBuilderSection;
use FluxErp\Helpers\ResponseHelper;
use FluxErp\Http\Requests\CreateFormBuilderSectionRequest;
use FluxErp\Http\Requests\UpdateFormBuilderFormRequest;
use FluxErp\Models\FormBuilderSection;
use Illuminate\Validation\ValidationException;

class FormBuilderSectionController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->model = new FormBuilderSection();
    }

    public function create(CreateFormBuilderSectionRequest $request)
    {
        return ResponseHelper::createResponseFromBase(
            statusCode: 201,
            data: CreateFormBuilderSection::make($request->validated())
                ->execute()
        );
    }

    public function update(UpdateFormBuilderFormRequest $request)
    {
        return ResponseHelper::createResponseFromBase(
            statusCode: 200,
            data: UpdateFormBuilderSection::make($request->validated())
                ->execute()
        );
    }

    public function delete(string $id): array
    {
        try {
            DeleteFormBuilderSection::make(['id' => $id])
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
            statusMessage: 'form builder section deleted'
        );
    }
}
