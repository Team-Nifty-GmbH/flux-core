<?php

namespace FluxErp\Traits\Livewire;

use FluxErp\Actions\Media\DownloadMedia;
use FluxErp\Actions\Media\DownloadMultipleMedia;
use FluxErp\Models\Media;
use FluxErp\Models\MediaFolder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Renderless;
use Spatie\Permission\Exceptions\UnauthorizedException;

trait SupportsFileDownloads
{
    use EnsureUsedInLivewire;

    #[Renderless]
    public function download(Media $media): void
    {
        if (! Storage::disk($media->disk)->exists($media->getPathRelativeToRoot())) {
            $this->toast()
                ->error(__('The file does not exist anymore.'))
                ->send();

            return;
        }

        try {
            DownloadMedia::make([
                'id' => $media->getKey(),
            ])
                ->checkPermission()
                ->validate();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return;
        }

        $this->redirect(URL::temporarySignedRoute(
            'media.show',
            now()->addMinutes(5),
            ['media' => $media->getKey(), 'download' => 1],
        ));
    }

    #[Renderless]
    public function downloadCollection(int|string $id, array|string $collection): void
    {
        // If $id is an integer we assume it's a media folder
        if (is_int($id)) {
            $mediaFolder = resolve_static(MediaFolder::class, 'familyTree')
                ->whereKey($id)
                ->first();

            if (! $mediaFolder) {
                return;
            }

            $fileName = trim($mediaFolder->name);
            $actionData = [
                'file_name' => $fileName,
                'media_folders' => [$mediaFolder->getKey()],
            ];
        } else {
            $collection = is_array($collection) ? implode('.', $collection) : $collection;
            $fileName = $collection;

            $actionData = [
                'media' => resolve_static(Media::class, 'query')
                    ->where('collection_name', 'like', $collection . '%')
                    ->when($this->modelType ?? false,
                        fn (Builder $query) => $query->where('model_type', morph_alias($this->modelType))
                            ->when(
                                $this->modelId ?? false,
                                fn ($query) => $query->where('model_id', $this->modelId)
                            )
                    )
                    ->pluck('id')
                    ->toArray(),
            ];
        }

        try {
            // Validate up-front so we don't redirect into a 403/422.
            DownloadMultipleMedia::make($actionData)
                ->checkPermission()
                ->validate();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return;
        }

        $token = Crypt::encrypt([
            'data' => $actionData,
            'name' => $fileName,
        ]);

        $this->redirect(URL::temporarySignedRoute(
            'media-collection.download',
            now()->addMinutes(5),
            ['token' => $token],
        ));
    }
}
