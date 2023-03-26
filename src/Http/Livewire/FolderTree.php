<?php

namespace FluxErp\Http\Livewire;

use FluxErp\Http\Requests\UpdateMediaRequest;
use FluxErp\Http\Requests\UploadMediaRequest;
use FluxErp\Services\MediaService;
use FluxErp\Traits\Livewire\WithFileUploads;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use WireUi\Traits\Actions;

class FolderTree extends Component
{
    use Actions, WithFileUploads;

    public array $tree = [];

    public ?string $modelType = null;

    public ?int $modelId = null;

    public bool $showDetails = false;

    public bool $showFolderDetails = false;

    public array $selected = [];

    public array $selectedCollection = [];

    public string $selectedCollectionName;

    protected $listeners = ['renderFromTree'];

    public string|array $upload = [];

    /**
     * @return array|mixed
     */
    public function getRules(): mixed
    {
        $rules = ($this->selected['id'] ?? false)
            ? (new UpdateMediaRequest())->rules()
            : (new UploadMediaRequest())->rules();

        return Arr::prependKeysWith($rules,
            'selected.');
    }

    public function mount(?string $modelType = null, ?int $modelId = null): void
    {
        $this->modelType = $modelType;
        $this->modelId = $modelId;

        $this->selected['disk'] = config('filesystems.default');

        if ($modelId && $modelType) {
            $this->tree = $modelType::query()->whereKey($modelId)->first()?->getMediaAsTree() ?: [];
        }

        if (! $this->tree) {
            $this->tree = [[
                'name' => 'files',
                'is_static' => true,
                'collection_name' => 'files',
                'children' => [],
            ]];
        }
    }

    public function render(): View|Factory|Application
    {
        return view('flux::livewire.folder-tree');
    }

    public function renderFromTree(?array $tree = []): void
    {
        $this->tree = $tree ?: [];
    }

    /**
     * @return BinaryFileResponse|void
     */
    public function select($id)
    {
        $media = Media::query()->whereKey($id)->first();
        $this->selected = $media->toArray();

        if (! auth()->user()->can('api.media.put')) {
            return $this->download();
        }

        $this->selected['preview'] = $media->toHtml();
        $this->selected['human_readable_size'] = $media->human_readable_size;
        $this->showDetails = true;

        $this->skipRender();
    }

    public function showFolder(string $collectionName, bool $isStatic): void
    {
        $this->selectedCollectionName = $collectionName;
        $exploded = explode('.', $collectionName);
        $this->selectedCollection['name'] = array_pop($exploded);
        $this->selectedCollection['path'] = implode('/', $exploded) ?: null;
        $this->selectedCollection['is_static'] = $isStatic;

        $this->collection = $collectionName;

        $this->showFolderDetails = true;
        $this->skipRender();
    }

    public function save(): void
    {
        if (! Auth::user()->can('api.media.put')) {
            return;
        }

        $this->validate();

        $service = new MediaService();

        $service->update($this->selected);
        $this->showDetails = false;
        $this->tree = $this->modelType::query()->whereKey($this->modelId)->first()?->getMediaAsTree() ?: [];

        $this->notification()->success(__('File saved!'));
    }

    public function saveFolder(): void
    {
        if (! Auth::user()->can('api.media.put') && ! Auth::user()->can('api.media.post')) {
            return;
        }

        $exploded = $this->selectedCollection['path']
            ? explode('/', $this->selectedCollection['path'])
            : [];
        $exploded[] = $this->selectedCollection['name'];
        $collectionName = implode('.', $exploded);

        if (
            $this->selectedCollectionName !== $this->selectedCollection['name']
            && ! $this->selectedCollection['is_static']
            && Auth::user()->can('api.media.put')
        ) {
            $media = Media::query()
                ->where('collection_name', 'LIKE', $this->selectedCollectionName . '%')
                ->get();

            foreach ($media as $mediaItem) {
                $mediaItem->collection_name = substr_replace(
                    $mediaItem->collection_name,
                    $collectionName,
                    0,
                    strlen($this->selectedCollectionName)
                );

                $mediaItem->save();
            }
        }

        if ($this->upload && Auth::user()->can('api.media.post')) {
            $this->collection = $collectionName;
            $this->saveFileUploadsToMediaLibrary('upload');
        }
        $this->tree = $this->modelType::query()->whereKey($this->modelId)->first()?->getMediaAsTree() ?: [];

        $this->notification()->success(__('Folder saved!'));

        $this->showFolderDetails = false;
    }

    public function delete(): void
    {
        if (! Auth::user()->can('api.media.{id}.delete')) {
            return;
        }

        Media::query()->whereKey($this->selected['id'])->delete();
        $this->tree = $this->modelType::query()->whereKey($this->modelId)->first()?->getMediaAsTree() ?: [];

        $this->showDetails = false;
    }

    public function deleteFolder(): void
    {
        if (! Auth::user()->can('api.media.{id}.delete')) {
            return;
        }

        Media::query()
            ->where('collection_name', 'LIKE', $this->selectedCollectionName . '%')
            ->delete();
        $this->tree = $this->modelType::query()->whereKey($this->modelId)->first()?->getMediaAsTree() ?: [];

        $this->showFolderDetails = false;
    }

    public function download(): BinaryFileResponse
    {
        $mediaItem = Media::query()->whereKey($this->selected['id'])->first();

        if (! file_exists($mediaItem->getPath())) {
            abort(404);
        }

        return response()->download($mediaItem->getPath(), $mediaItem->name);
    }
}
