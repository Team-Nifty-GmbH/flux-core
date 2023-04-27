<?php

namespace FluxErp\Http\Controllers;

use FluxErp\Helpers\Helper;
use FluxErp\Helpers\ResponseHelper;
use FluxErp\Http\Requests\CreateCategoryRequest;
use FluxErp\Models\Category;
use FluxErp\Services\CategoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CategoryController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->model = new Category();
    }

    /**
     * @throws \Illuminate\Validation\ValidationException
     */
    public function create(Request $request, CategoryService $categoryService): JsonResponse
    {
        $data = $request->all();
        if (! ($data['model_type'] ?? false)) {
            return ResponseHelper::createResponseFromBase(
                statusCode: 422,
                data: ['model_type' => __('the model_type field is required')]
            );
        }

        $model = Helper::classExists(classString: $data['model_type'], isModel: true);
        if (! $model) {
            return ResponseHelper::createResponseFromBase(
                statusCode: 404,
                data: ['model_type' => __('model not found')]
            );
        }

        $data['model_type'] = $model;
        $data['sort_number'] = 0;

        $validation = array_merge(
            (new CreateCategoryRequest())->rules(),
            [
                'parent_id' => 'integer|nullable|exists:categories,id,model_type,' . $model,
                'sort_number' => 'required|integer',
            ]
        );

        $validator = Validator::make($data, $validation);
        $validator->addModel($this->model);

        if ($validator->fails()) {
            return ResponseHelper::createResponseFromBase(
                statusCode: 422,
                data: $validator->errors()->toArray()
            );
        }

        $category = $categoryService->create($validator->validated());

        return ResponseHelper::createResponseFromBase(
            statusCode: 201,
            data: $category,
            statusMessage: 'category created'
        );
    }

    public function update(Request $request, CategoryService $categoryService): JsonResponse
    {
        $response = $categoryService->update($request->all());

        return ResponseHelper::createResponseFromArrayResponse($response);
    }

    public function delete(string $id, CategoryService $categoryService): JsonResponse
    {
        $response = $categoryService->delete($id);

        return ResponseHelper::createResponseFromArrayResponse($response);
    }
}
