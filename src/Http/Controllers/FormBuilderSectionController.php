<?php

namespace FluxErp\Http\Controllers;

use FluxErp\Actions\FormBuilderSection\CreateFormBuilderSection;
use FluxErp\Actions\FormBuilderSection\DeleteFormBuilderSection;
use FluxErp\Actions\FormBuilderSection\UpdateFormBuilderSection;
use FluxErp\Helpers\ResponseHelper;
use FluxErp\Models\FormBuilderSection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class FormBuilderSectionController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->model = app(FormBuilderSection::class);
    }

    public function create(Request $request): JsonResponse
    {
        $formBuilderSection = CreateFormBuilderSection::make($request->all())
            ->validate()
            ->execute();

        return ResponseHelper::createResponseFromBase(
            statusCode: 201,
            data: $formBuilderSection
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

    public function update(Request $request): JsonResponse
    {
        $formBuilderSection = UpdateFormBuilderSection::make($request->all())
            ->validate()
            ->execute();

        return ResponseHelper::createResponseFromBase(
            statusCode: 200,
            data: $formBuilderSection
        );
    }
}
