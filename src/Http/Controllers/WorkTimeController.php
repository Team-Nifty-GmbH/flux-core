<?php

namespace FluxErp\Http\Controllers;

use FluxErp\Helpers\QueryBuilder;
use FluxErp\Helpers\ResponseHelper;
use FluxErp\Models\WorkTime;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WorkTimeController extends Controller
{
    public function userIndex(Request $request): JsonResponse
    {
        $page = max((int) $request->page, 1);
        $perPage = $request->per_page > 500 || $request->per_page < 1 ? 25 : $request->per_page;

        $query = QueryBuilder::filterModel(app(WorkTime::class), $request);
        $data = $query
            ->where('user_id', $request->user()->id)
            ->paginate(perPage: $perPage, page: $page)
            ->appends($request->query());

        return ResponseHelper::createResponseFromBase(
            statusCode: 200,
            data: $data,
        )
            ->setEncodingOptions(JSON_UNESCAPED_SLASHES);
    }
}
