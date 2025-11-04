<?php

namespace FluxErp\Livewire\Support;

use Exception;
use FluxErp\Actions\Media\DeleteMedia;
use FluxErp\Actions\Media\DeleteMediaCollection;
use FluxErp\Actions\MediaFolder\DeleteMediaFolder;
use FluxErp\Actions\MediaFolder\UpdateMediaFolder;
use FluxErp\Livewire\Forms\MediaFolderForm;
use FluxErp\Models\Media;
use FluxErp\Models\Media as MediaModel;
use FluxErp\Models\MediaFolder;
use FluxErp\Traits\Livewire\Actions;
use FluxErp\Traits\Livewire\WithFilePond;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Modelable;
use Livewire\Attributes\Renderless;
use Livewire\Component;
use Spatie\Permission\Exceptions\UnauthorizedException;

abstract class FolderTree extends Component
{
    use Actions, WithFilePond;

    public $files = [];

    public MediaFolderForm $folder;

    #[Modelable]
    public ?int $modelId = null;

    #[Locked]
    public bool $isReadonly = false;

    /** @var class-string<Model> */
    protected string $modelType;

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

    public function deleteCollection(int|string $id, string|array|null $collection = null): bool
    {
        if (is_int($id)) {
            try {
                DeleteMediaFolder::make([
                    'id' => $id,
                    'model_type' => morph_alias($this->modelType),
                    'model_id' => $this->modelId,
                ])
                    ->checkPermission()
                    ->validate()
                    ->execute();
            } catch (UnauthorizedException|ValidationException $e) {
                exception_to_notifications($e, $this);

                return false;
            }

            return true;
        }

        if (! $collection) {
            return false;
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
        } catch (UnauthorizedException|ValidationException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        return true;
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

    public function getTree(array $exclude = []): array
    {
        if (! $this->modelType || ! $this->modelId) {
            return [];
        }

        return resolve_static($this->modelType, 'query')
            ->whereKey($this->modelId)
            ->first()
            ?->getMediaAsTree($exclude) ?? [];
    }

    #[Renderless]
    public function hasSingleFile(int|string|null $id, string $collectionName): bool
    {
        if (is_null($id)) {
            return false;
        }

        // get the base collection name - ignore subfolders
        $baseCollection = str_contains($collectionName, '.') ?
            explode('.', $collectionName)[0] : $collectionName;

        return resolve_static(MediaFolder::class, 'query')
            ->whereKey($id)
            ->where('max_files', 1)
            ->exists()
            ?: (
                data_get(
                    resolve_static($this->modelType, 'query')
                        ->whereKey($this->modelId)
                        ->first()
                        ?->getMediaCollection($baseCollection),
                    'singleFile',
                )
                ?? false
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
    public function moveItem(array $subject, array $target, string $subjectPath, string $targetPath): void
    {
        $subjectType = match (true) {
            ! is_null(data_get($subject, 'file_name')) => 'media',
            is_int(data_get($subject, 'id')) => 'folder',
            default => 'collection',
        };

        $targetType = match (true) {
            is_int(data_get($target, 'id')) => 'folder',
            default => 'collection',
        };

        if ($this->isReadonly
            || ($subjectType === 'folder' && $targetType !== 'folder')
            || ($subjectType === 'collection' && $targetType !== 'collection')
            || ($subjectType !== 'media' && $this->readOnly(data_get($subject, 'id'), $subjectPath))
            || $this->readOnly(data_get($target, 'id'), $targetPath)
        ) {
            return;
        }

        if ($subjectType === 'folder' && $targetType === 'folder') {
            try {
                UpdateMediaFolder::make([
                    'id' => data_get($subject, 'id'),
                    'parent_id' => data_get($target, 'id'),
                    'model_type' => morph_alias($this->modelType),
                    'model_id' => $this->modelId,
                ])
                    ->checkPermission()
                    ->validate()
                    ->execute();
            } catch (UnauthorizedException|ValidationException $e) {
                exception_to_notifications($e, $this);

                return;
            }
        }

        $newCollectionName = $targetPath . '.'
            . Str::of(data_get($subject, 'name') ?? '')
                ->replace('.', '_')
                ->snake()
                ->toString();
        if ($subjectType === 'collection' && $targetType === 'collection') {
            if ($newCollectionName !== $subjectPath) {
                resolve_static(Media::class, 'query')
                    ->where('model_type', morph_alias($this->modelType))
                    ->where('model_id', $this->modelId)
                    ->where('collection_name', 'like', $subjectPath . '%')
                    ->update([
                        'collection_name' => DB::raw('CONCAT(\'' . $newCollectionName
                            . '\', SUBSTRING(collection_name, ' . strlen($subjectPath) + 1 . '))'
                        ),
                    ]);
            }

            return;
        }

        // Now only moving media to media folder or collection is left
        if ($targetType === 'collection') {
            $model = resolve_static($this->modelType, 'query')
                ->whereKey($this->modelId)
                ->first();
        } else {
            $model = resolve_static(MediaFolder::class, 'query')
                ->whereKey(data_get($target, 'id'))
                ->first();
        }

        resolve_static(MediaModel::class, 'query')
            ->where('model_type', morph_alias($this->modelType))
            ->where('model_id', $this->modelId)
            ->with('model')
            ->whereKey(data_get($subject, 'id'))
            ->first()
            ?->move($model, $newCollectionName);
    }

    #[Renderless]
    public function readOnly(int|string|null $id, string $collectionName): bool
    {
        if (is_null($id)) {
            return false;
        }

        // get the base collection name - ignore subfolders
        $baseCollection = str_contains($collectionName, '.') ?
            explode('.', $collectionName)[0] : $collectionName;

        // in case there is no rule for the folder - $baseCollection
        // enable upload
        return resolve_static(MediaFolder::class, 'query')
            ->whereKey($id)
            ->where('is_readonly', true)
            ->exists()
            ?: (
                data_get(
                    resolve_static($this->modelType, 'query')
                        ->whereKey($this->modelId)
                        ->first()
                        ?->getMediaCollection($baseCollection),
                    'readOnly'
                )
                ?? false
            );
    }

    #[Renderless]
    public function saveFolder(array $attributes): false|array
    {
        if (is_string(data_get($attributes, 'parent_id')) || is_string(data_get($attributes, 'id'))) {
            $attributes['slug'] = Str::of(data_get($attributes, 'name') ?? '')
                ->replace('.', '_')
                ->snake()
                ->toString();
            $path = data_get($attributes, 'path') ?? '';
            $replace = Str::replaceLast(Str::afterLast($path, '.'), $attributes['slug'], $path);

            if ($path !== $replace) {
                resolve_static(Media::class, 'query')
                    ->where('model_type', morph_alias($this->modelType))
                    ->where('model_id', $this->modelId)
                    ->where('collection_name', 'like', $path . '%')
                    ->update([
                        'collection_name' => DB::raw('CONCAT(\'' . $replace
                            . '\', SUBSTRING(collection_name, ' . strlen($path) + 1 . '))'
                        ),
                    ]);
            }

            return $attributes;
        }

        $this->folder->reset();
        $this->folder->fill($attributes);

        try {
            $this->folder->reset();
            $this->folder->fill($attributes);

            $this->folder->model_type = morph_alias($this->modelType);
            $this->folder->model_id = $this->modelId;
            $this->folder->save();
        } catch (UnauthorizedException|ValidationException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        return array_merge(
            ['children' => []],
            array_intersect_key(
                $this->folder->getActionResult()->toArray(),
                array_flip([
                    'id',
                    'name',
                    'slug',
                    'is_readonly',
                    'is_static',
                    'children',
                ])
            )
        );
    }
}
