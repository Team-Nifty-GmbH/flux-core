<?php

namespace FluxErp\Http\Controllers;

use FluxErp\Models\Media;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PrivateMediaController extends Controller
{
    public function __invoke(
        Request $request,
        Media $media,
    ): Media|BinaryFileResponse|StreamedResponse|RedirectResponse {
        $conversion = (string) $request->query('conversion', '');

        if (blank($conversion)) {
            return $media;
        }

        abort_unless($media->hasGeneratedConversion($conversion), 404);

        $disk = Storage::disk($media->conversions_disk);
        $relativePath = $media->getPathRelativeToRoot($conversion);
        $fileName = basename($relativePath);

        $disposition = HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_INLINE,
            $fileName,
        );

        if ($disk->providesTemporaryUrls()) {
            return redirect()->away(
                $disk->temporaryUrl(
                    $relativePath,
                    now()->addMinutes(5),
                    ['ResponseContentDisposition' => $disposition],
                )
            );
        }

        return $disk->response(
            $relativePath,
            $fileName,
            ['Content-Disposition' => $disposition],
        );
    }
}
