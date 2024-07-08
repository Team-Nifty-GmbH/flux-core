<?php

namespace FluxErp\Http\Controllers;

use FluxErp\Helpers\ResponseHelper;
use FluxErp\Http\Requests\DownloadPublicMediaRequest;
use FluxErp\Models\Media;
use FluxErp\Services\MediaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\Support\MediaStream;

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

    public function upload(Request $request, MediaService $mediaService): JsonResponse
    {
        $response = $mediaService->upload($request->all());

        return ResponseHelper::createResponseFromArrayResponse($response)
            ->setEncodingOptions(JSON_UNESCAPED_SLASHES);
    }

    public function replace(string $id, Request $request, MediaService $mediaService): JsonResponse
    {
        $response = $mediaService->replace($id, $request->all());

        return ResponseHelper::createResponseFromArrayResponse($response)
            ->setEncodingOptions(JSON_UNESCAPED_SLASHES);
    }

    public function update(Request $request, MediaService $mediaService): JsonResponse
    {
        $media = $mediaService->update($request->all());

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

    public function deleteCollection(Request $request, MediaService $mediaService): JsonResponse
    {
        $response = $mediaService->deleteCollection($request->all());

        return ResponseHelper::createResponseFromArrayResponse($response);
    }

    public function downloadMultiple(Request $request)
    {
        $ids = $request->input('ids');
        $fileName = Str::finish($request->input('filename') ?? 'media', '.zip');
        $ids = is_array($ids) ? $ids : explode(',', $ids);

        if (! $ids) {
            abort(422);
        }

        $media = app(Media::class)->query()->whereIntegerInRaw('id', $ids)->get();

        return MediaStream::create($fileName)
            ->addMedia($media);
    }
}
