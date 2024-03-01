<?php

namespace FluxErp\Http\Controllers;

use FluxErp\Helpers\ResponseHelper;
use FluxErp\Models\Category;
use FluxErp\Services\CategoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->model = app(Category::class);
    }

    /**
     * @throws \Illuminate\Validation\ValidationException
     */
    public function create(Request $request, CategoryService $categoryService): JsonResponse
    {
        $category = $categoryService->create($request->all());

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
