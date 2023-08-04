<?php

namespace FluxErp\Http\Controllers;

use FluxErp\Helpers\ResponseHelper;
use FluxErp\Http\Requests\DeleteMediaCollectionRequest;
use FluxErp\Http\Requests\DownloadPublicMediaRequest;
use FluxErp\Http\Requests\ReplaceMediaRequest;
use FluxErp\Http\Requests\UpdateMediaRequest;
use FluxErp\Http\Requests\UploadMediaRequest;
use FluxErp\Services\MediaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MediaController extends Controller
{
    /**
     * @throws \League\Flysystem\FilesystemException
     */
    public function downloadPublic(string $filename,
        DownloadPublicMediaRequest $request,
        MediaService $mediaService): JsonResponse
    {
        $response = $mediaService->downloadPublic($filename, $request->validated());

        return ResponseHelper::createResponseFromArrayResponse($response);
    }

    /**
     * @throws \League\Flysystem\FilesystemException
     */
    public function download(string $id, Request $request, MediaService $mediaService): JsonResponse
    {
        $response = $mediaService->download($id, $request->all());

        return ResponseHelper::createResponseFromArrayResponse($response);
    }

    public function upload(UploadMediaRequest $request, MediaService $mediaService): JsonResponse
    {
        $response = $mediaService->upload($request->validated());

        return ResponseHelper::createResponseFromArrayResponse($response)
            ->setEncodingOptions(JSON_UNESCAPED_SLASHES);
    }

    public function replace(string $id, ReplaceMediaRequest $request, MediaService $mediaService): JsonResponse
    {
        $response = $mediaService->replace($id, $request->validated());

        return ResponseHelper::createResponseFromArrayResponse($response)
            ->setEncodingOptions(JSON_UNESCAPED_SLASHES);
    }

    public function update(UpdateMediaRequest $request, MediaService $mediaService): JsonResponse
    {
        $media = $mediaService->update($request->validated());

        return ResponseHelper::createResponseFromBase(
            statusCode: 200,
            data: $media,
            additions: ['url' => $media->getUrl()]
        )->setEncodingOptions(JSON_UNESCAPED_SLASHES);
    }

    public function delete(string $id, MediaService $mediaService): JsonResponse
    {
        $response = $mediaService->delete($id);

        return ResponseHelper::createResponseFromArrayResponse($response);
    }

    public function deleteCollection(DeleteMediaCollectionRequest $request, MediaService $mediaService): JsonResponse
    {
        $response = $mediaService->deleteCollection($request->validated());

        return ResponseHelper::createResponseFromArrayResponse($response);
    }
}
