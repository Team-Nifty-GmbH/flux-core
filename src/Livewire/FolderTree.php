<?php

namespace FluxErp\Livewire;

use Exception;
use FluxErp\Actions\Media\DeleteMedia;
use FluxErp\Actions\Media\DeleteMediaCollection;
use FluxErp\Actions\Media\UpdateMedia;
use FluxErp\Models\Media as MediaModel;
use FluxErp\Traits\Livewire\Actions;
use FluxErp\Traits\Livewire\WithFilePond;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Livewire\Attributes\Renderless;
use Livewire\Component;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Permission\Exceptions\UnauthorizedException;

class FolderTree extends Component
{
    use Actions, WithFilePond;

    public $files = [];

    public ?int $modelId = null;

    /** @var class-string<Model> */
    public ?string $modelType = null;

    public function render(): View|Factory|Application
    {
        return view('flux::livewire.folder-tree');
    }

    public function delete(MediaModel $media): bool
    {
        try {
            DeleteMedia::make($media->toArray())
                ->checkPermission()
                ->validate()
                ->execute();
        } catch (Exception $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        return true;
    }

    public function deleteCollection(string|array|null $collection = null): void
    {
        if (! $collection) {
            return;
        }

        $collection = is_array($collection) ? implode('.', $collection) : $collection;

        try {
            DeleteMediaCollection::make([
                'model_type' => morph_alias($this->modelType),
                'model_id' => $this->modelId,
                'collection_name' => $collection,
            ])
                ->checkPermission()
                ->validate()
                ->execute();
        } catch (Exception $e) {
            exception_to_notifications($e, $this);
        }
    }

    public function getListeners(): array
    {
        return [
            'folder-tree:loadModel' => 'loadModel',
        ];
    }

    public function getRules(): array
    {
        return [
            'files.*' => 'required|' . (config('livewire.temporary_file_upload.rules') ?? 'file|max:12288'),
        ];
    }

    public function getTree(): array
    {
        if (! $this->modelType || ! $this->modelId) {
            return [];
        }

        return resolve_static($this->modelType, 'query')
            ->whereKey($this->modelId)
            ->first()
            ?->getMediaAsTree() ?? [];
    }

    #[Renderless]
    public function hasSingleFile(string $collectionName): bool
    {
        // get the base collection name - ignore subfolders
        $baseCollection = str_contains($collectionName, '.') ?
            explode('.', $collectionName)[0] : $collectionName;

        return data_get(
            resolve_static($this->modelType, 'query')
                ->whereKey($this->modelId)
                ->first()
                ?->getMediaCollection($baseCollection),
            'singleFile',
            false
        );
    }

    public function loadModel(array $arguments): void
    {
        $this->fill($arguments);

        $this->js(
            <<<'JS'
                selected = false;
                loadLevels();
            JS
        );
    }

    #[Renderless]
    public function moveItem(string $mediaId, array|string $targetCollectionName): void
    {
        $targetCollectionName = is_array($targetCollectionName)
            ? implode('.', $targetCollectionName)
            : $targetCollectionName;
        $model = resolve_static($this->modelType, 'query')
            ->whereKey($this->modelId)
            ->first();

        if (! is_numeric($mediaId)) {
            resolve_static(MediaModel::class, 'query')
                ->where('model_type', morph_alias($this->modelType))
                ->where('model_id', $this->modelId)
                ->where('collection_name', 'LIKE', $mediaId . '%')
                ->get()
                ->each(function (MediaModel $media) use ($mediaId, $targetCollectionName, $model): void {
                    $collectionName = $media->collection_name;
                    $collectionName = Str::replaceFirst(Str::beforeLast($mediaId, '.'), $targetCollectionName, $collectionName);

                    $media->move($model, $collectionName);
                });
        } else {
            resolve_static(MediaModel::class, 'query')
                ->where('model_type', morph_alias($this->modelType))
                ->where('model_id', $this->modelId)
                ->with('model')
                ->whereKey($mediaId)
                ->first()
                ?->move($model, $targetCollectionName);
        }

    }

    #[Renderless]
    public function readOnly(string $collectionName): bool
    {
        // get the base collection name - ignore subfolders
        $baseCollection = str_contains($collectionName, '.') ?
            explode('.', $collectionName)[0] : $collectionName;

        // in case there is no rule for the folder - $baseCollection
        // enable upload
        return data_get(
            resolve_static($this->modelType, 'query')
                ->whereKey($this->modelId)
                ->first()
                ?->getMediaCollection($baseCollection),
            'readOnly',
            false
        );
    }

    public function save(array $item): bool
    {
        try {
            resolve_static(UpdateMedia::class, 'canPerformAction');
        } catch (UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        return ($item['file_name'] ?? false) ? $this->saveFile($item) : $this->saveFolder($item);
    }

    #[Renderless]
    public function saveFolder(array $collection): true
    {
        $newCollectionName = explode('.', $collection['collection_name']);

        array_pop($newCollectionName);
        $newCollectionName[] = Str::of($collection['name'])
            ->ascii(config('app.locale'))
            ->snake();
        $newCollectionName = implode('.', $newCollectionName);

        $model = resolve_static($this->modelType, 'query')
            ->whereKey($this->modelId)
            ->first();

        resolve_static(MediaModel::class, 'query')
            ->where('model_type', morph_alias($this->modelType))
            ->where('model_id', $this->modelId)
            ->where('collection_name', 'LIKE', $collection['collection_name'] . '%')
            ->get()
            ->each(function (MediaModel $media) use ($newCollectionName, $model, $collection): void {
                $collectionName = $media->collection_name;
                $collectionName = Str::replaceFirst($collection['collection_name'], $newCollectionName, $collectionName);

                $media->move($model, $collectionName);
            });

        return true;
    }

    #[Renderless]
    public function updatedFiles(): void
    {
        try {
            resolve_static(UpdateMedia::class, 'canPerformAction', [false]);
        } catch (UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return;
        }
    }

    private function saveFile(array $media): bool
    {
        try {
            $response = UpdateMedia::make($media)
                ->checkPermission()
                ->validate()
                ->execute();
        } catch (Exception $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->notification()->success(__('File saved!'))->send();

        return $response instanceof Media;
    }
}
