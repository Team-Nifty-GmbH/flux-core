<?php

namespace FluxErp\Http\Controllers;

use FluxErp\Helpers\Helper;
use FluxErp\Helpers\ResponseHelper;
use FluxErp\Http\Requests\CreateCommentRequest;
use FluxErp\Services\CommentService;
use FluxErp\Traits\Commentable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function show(string $modelType, string $id): JsonResponse
    {
        $model = Helper::classExists(classString: $modelType, isModel: $modelType);

        $traits = $model ? class_uses($model) : [];
        if (! $model || ! array_key_exists(Commentable::class, $traits)) {
            return ResponseHelper::createResponseFromBase(statusCode: 404, data: ['route' => 'route not found']);
        }

        $modelInstance = new $model();
        $modelInstance = $modelInstance::query()
            ->whereKey($id)
            ->first();

        if (! $modelInstance) {
            return ResponseHelper::createResponseFromBase(statusCode: 404, data: ['id' => 'model instance not found']);
        }

        return ResponseHelper::createResponseFromBase(
            statusCode: 200,
            data: $modelInstance->comments()->orderBy('comments.id', 'DESC')->fastPaginate()
        );
    }

    public function create(CreateCommentRequest $request, CommentService $commentService): JsonResponse
    {
        $response = $commentService->create($request->validated());

        return ResponseHelper::createResponseFromArrayResponse($response);
    }

    public function update(Request $request, CommentService $commentService): JsonResponse
    {
        $response = $commentService->update($request->all());

        return ResponseHelper::createResponseFromArrayResponse($response);
    }

    public function delete(string $id, CommentService $commentService): JsonResponse
    {
        $response = $commentService->delete($id);

        return ResponseHelper::createResponseFromArrayResponse($response);
    }
}
