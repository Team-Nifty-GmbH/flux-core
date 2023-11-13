<?php

namespace FluxErp\Http\Controllers;

use FluxErp\Actions\FormBuilderSection\CreateFormBuilderSection;
use FluxErp\Actions\FormBuilderSection\DeleteFormBuilderSection;
use FluxErp\Actions\FormBuilderSection\UpdateFormBuilderSection;
use FluxErp\Helpers\ResponseHelper;
use FluxErp\Http\Requests\CreateFormBuilderSectionRequest;
use FluxErp\Http\Requests\UpdateFormBuilderSectionRequest;
use FluxErp\Models\FormBuilderSection;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class FormBuilderSectionController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->model = new FormBuilderSection();
    }

    public function create(CreateFormBuilderSectionRequest $request): JsonResponse
    {
        return ResponseHelper::createResponseFromBase(
            statusCode: 201,
            data: CreateFormBuilderSection::make($request->validated())
                ->execute()
        );
    }

    public function update(UpdateFormBuilderSectionRequest $request): JsonResponse
    {
        return ResponseHelper::createResponseFromBase(
            statusCode: 200,
            data: UpdateFormBuilderSection::make($request->validated())
                ->execute()
        );
    }

    public function delete(string $id): JsonResponse
    {
        try {
            DeleteFormBuilderSection::make(['id' => $id])
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
            statusMessage: 'form builder section deleted'
        );
    }
}
