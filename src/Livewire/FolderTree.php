<?php

namespace FluxErp\Livewire;

use FluxErp\Actions\Media\DeleteMedia;
use FluxErp\Actions\Media\DeleteMediaCollection;
use FluxErp\Actions\Media\UpdateMedia;
use FluxErp\Helpers\ResponseHelper;
use FluxErp\Models\Media as MediaModel;
use FluxErp\Traits\Livewire\WithFileUploads;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Renderless;
use Livewire\Component;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Permission\Exceptions\UnauthorizedException;
use WireUi\Traits\Actions;

class FolderTree extends Component
{
    use Actions, WithFileUploads;

    /** @var Model $this->modelType */
    public ?string $modelType = null;

    public ?int $modelId = null;

    public array $files = [];

    public array $latestUploads = [];

    public function getListeners(): array
    {
        return [
            'folder-tree:loadModel' => 'loadModel',
        ];
    }

    public function loadModel(array $arguments): void
    {
        $this->fill($arguments);

        $this->js(<<<'JS'
            selected = false;
            loadLevels();
        JS);
    }

    public function getRules(): array
    {
        return [
            'files.*'=> 'required|file|max:1024',
        ];
    }

    public function getRulesSingleFile($index): array
    {
        return [
            'files.'.$index => 'required|file|max:1024',
        ];
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

    #[Renderless]
    public function validateOnDemand(string $fileId):bool
    {

        $index = array_search($fileId, array_map(function ($item) {
            return $item->getFilename();
        } ,$this->files));

        if($index === false) {
            return false;
        }

        try {
            $this->validate($this->getRulesSingleFile($index));
        } catch (ValidationException $e) {
            // Handle the validation exception
            exception_to_notifications($e, $this);
            return false;
        }

        return true;
    }

    #[Renderless]
    public function submitFiles(string $collection, array $tempFileNames): bool
    {
        // set the folder name
        $this->collection = $collection;
        // filter out files array by deleted files on front end
        $this->files = array_filter($this->files, function ($file) use ($tempFileNames) {
            return in_array($file->getFilename(), $tempFileNames);
        });

        // validation took place in updatedFiles method
        // so we can safely save the files
        try {
            $media = $this->saveFileUploadsToMediaLibrary(
                name: 'files',
                modelId: $this->modelId,
                modelType: app($this->modelType)->getMorphClass(),
            );
            $this->latestUploads = $media;
            $this->files = [];
        } catch (\Exception $e) {
            exception_to_notifications($e, $this);
            return false;
        }

        return true;
    }

    public function mount(?string $modelType = null, ?int $modelId = null): void
    {
        $this->modelType = $modelType;
        $this->modelId = $modelId;
    }

    public function render(): View|Factory|Application
    {
        return view('flux::livewire.folder-tree');
    }

    public function getTree(): array
    {
        if (! $this->modelType || ! $this->modelId) {
            return [];
        }

        return app($this->modelType)->query()->whereKey($this->modelId)->first()?->getMediaAsTree() ?: [];
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

    public function saveFolder(array $collection): true
    {
        $newCollectionName = explode('.', $collection['collection_name']);
        array_pop($newCollectionName);
        $newCollectionName[] = Str::of($collection['name'])
            ->ascii(config('app.locale'))
            ->snake();
        $newCollectionName = implode('.', $newCollectionName);

        $model = app($this->modelType)->query()->whereKey($this->modelId)->first();

        resolve_static(MediaModel::class, 'query')
            ->where('model_type', app($this->modelType)->getMorphClass())
            ->where('model_id', $this->modelId)
            ->where('collection_name', 'LIKE', $collection['collection_name'].'%')
            ->get()
            ->each(function (MediaModel $media) use ($newCollectionName, $model, $collection) {
                $collectionName = $media->collection_name;
                $collectionName = str_replace($collection['collection_name'], $newCollectionName, $collectionName);

                $media->move($model, $collectionName);
            });

        return true;
    }

    public function delete(MediaModel $media): bool
    {
        try {
            DeleteMedia::make($media->toArray())
                ->checkPermission()
                ->validate()
                ->execute();
        } catch (\Exception $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        return true;
    }

    public function deleteCollection(string $collection): void
    {
        try {
            DeleteMediaCollection::make([
                'model_type' => app($this->modelType)->getMorphClass(),
                'model_id' => $this->modelId,
                'collection_name' => $collection,
            ])
                ->checkPermission()
                ->validate()
                ->execute();
        } catch (\Exception $e) {
            exception_to_notifications($e, $this);
        }
    }

    private function saveFile(array $media): bool
    {
        try {
            $response = UpdateMedia::make($media)
                ->checkPermission()
                ->validate()
                ->execute();
        } catch (\Exception $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->notification()->success(__('File saved!'));

        return $response instanceof Media;
    }
}
