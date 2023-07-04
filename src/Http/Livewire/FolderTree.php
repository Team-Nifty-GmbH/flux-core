<?php

namespace FluxErp\Http\Livewire;

use FluxErp\Http\Requests\UpdateMediaRequest;
use FluxErp\Services\MediaService;
use FluxErp\Traits\Livewire\WithFileUploads;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Livewire\Component;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use WireUi\Traits\Actions;

class FolderTree extends Component
{
    use Actions, WithFileUploads;

    /** @var Model $this->modelType */
    public ?string $modelType = null;

    public ?int $modelId = null;

    public $files = [];

    public array $latestUploads = [];

    /**
     * @return array|mixed
     */
    public function getRules(): mixed
    {
        return [
            'files.*' => 'required|file|max:10240',
        ];
    }

    public function updatedFiles(): void
    {
        if (! Auth::user()->can('api.media.post')) {
            return;
        }

        $this->validate($this->getRules());

        $media = $this->saveFileUploadsToMediaLibrary(
            name: 'files',
            modelId: $this->modelId,
            modelType: $this->modelType
        );

        $this->latestUploads = $media;
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

        return $this->modelType::query()->whereKey($this->modelId)->first()?->getMediaAsTree() ?: [];
    }

    public function save(array $item): bool
    {
        if (! Auth::user()->can('api.media.put')) {
            return false;
        }

        return ($item['file_name'] ?? false) ? $this->saveFile($item) : $this->saveFolder($item);
    }

    public function saveFolder(array $collection): bool
    {
        $newCollectionName = explode('.', $collection['collection_name']);
        array_pop($newCollectionName);
        $newCollectionName[] = Str::of($collection['name'])
            ->ascii(config('app.locale'))
            ->snake();
        $newCollectionName = implode('.', $newCollectionName);

        $model = $this->modelType::query()->whereKey($this->modelId)->first();

        \FluxErp\Models\Media::query()
            ->where('model_type', $this->modelType)
            ->where('model_id', $this->modelId)
            ->where('collection_name', 'LIKE', $collection['collection_name'] . '%')
            ->get()
            ->each(function (\FluxErp\Models\Media $media) use ($newCollectionName, $model, $collection) {
                $collectionName = $media->collection_name;
                $collectionName = str_replace($collection['collection_name'], $newCollectionName, $collectionName);

                $media->move($model, $collectionName);
            });

        return true;
    }

    public function delete(\FluxErp\Models\Media $media): bool
    {
        if (! Auth::user()->can('api.media.{id}.delete')) {
            return false;
        }

        return $media->delete();
    }

    public function deleteCollection(string $collection): void
    {
        if (! Auth::user()->can('api.media.{id}.delete')) {
            return;
        }

        Media::query()
            ->where('model_type', $this->modelType)
            ->where('model_id', $this->modelId)
            ->where('collection_name', 'LIKE', $collection . '%')
            ->delete();
    }

    private function saveFile(array $media): bool
    {
        $validator = Validator::make($media, (new UpdateMediaRequest())->rules());
        $validated = $validator->validate();

        $service = new MediaService();
        $response = $service->update($validated);

        $this->notification()->success(__('File saved!'));

        return $response instanceof Media;
    }
}
