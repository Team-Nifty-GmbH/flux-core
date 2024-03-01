<?php

namespace FluxErp\Http\Controllers;

use FluxErp\Helpers\ResponseHelper;
use FluxErp\Models\DiscountGroup;
use FluxErp\Services\DiscountGroupService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DiscountGroupController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->model = app(DiscountGroup::class);
    }

    public function create(Request $request, DiscountGroupService $discountGroupService): JsonResponse
    {
        $discountGroup = $discountGroupService->create($request->all());

        return ResponseHelper::createResponseFromBase(
            statusCode: 201,
            data: $discountGroup,
            statusMessage: 'discount group created'
        );
    }

    public function update(Request $request, DiscountGroupService $discountGroupService): JsonResponse
    {
        $response = $discountGroupService->update($request->all());

        return ResponseHelper::createResponseFromArrayResponse($response);
    }

    public function delete(string $id, DiscountGroupService $discountGroupService): JsonResponse
    {
        $response = $discountGroupService->delete($id);

        return ResponseHelper::createResponseFromArrayResponse($response);
    }
}
