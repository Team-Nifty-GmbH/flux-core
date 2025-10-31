<?php

namespace FluxErp\Traits;

use FluxErp\Models\Media as FluxMedia;
use FluxErp\Models\MediaFolder;
use FluxErp\Support\MediaLibrary\MediaCollection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Spatie\Image\Exceptions\InvalidManipulation;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

trait InteractsWithMedia
{
    use \Spatie\MediaLibrary\InteractsWithMedia;

    public function addMediaCollection(string $name): MediaCollection
    {
        $mediaCollection = MediaCollection::create($name);

        $this->mediaCollections[$name] = $mediaCollection;

        return $mediaCollection;
    }

    public function getMediaAsTree(array $exclude = []): array
    {
        $mediaFolders = resolve_static(MediaFolder::class, 'familyTree')
            ->whereKey($this->mediaFolders()->pluck('id')->toArray())
            ->get()
            ->flatten();

        $mediaCollections = collect(
            array_merge(
                $registeredMediaCollections = $this->getRegisteredMediaCollections()
                    ->map(fn ($mediaCollection) => [
                        'name' => __(Str::headline($mediaCollection->name)),
                        'slug' => $mediaCollection->name,
                        'is_static' => true,
                    ])
                    ->filter(fn (array $mediaCollection) => ! in_array($mediaCollection['slug'], $exclude, true))
                    ->toArray(),
                $this->media()
                    ->when($exclude, function (Builder $query) use ($exclude): void {
                        $query->where(function (Builder $query) use ($exclude): void {
                            foreach ($exclude as $collection) {
                                $query->where(
                                    'collection_name',
                                    'NOT LIKE',
                                    $collection . '%',
                                );
                            }
                        });
                    })
                    ->groupBy('collection_name')
                    ->pluck('collection_name')
                    ->filter(
                        fn ($collectionName) => ! in_array(
                            $collectionName,
                            data_get($registeredMediaCollections, '*.slug'),
                            true
                        )
                    )
                    ->map(fn ($media) => [
                        'name' => Str::headline(Str::afterLast($media, '.')),
                        'slug' => $media,
                    ])
                    ->toArray(),
                $mediaFolders->toArray()
            )
        )
            ->sortBy('slug')
            ->toArray();

        $undotted = [];
        foreach ($mediaCollections as $mediaCollection) {
            $slug = data_get($mediaCollection, 'slug') ?? '';
            if (
                is_null(data_get($mediaCollection, 'id'))
                && str_contains($slug, '.')
            ) {
                $path = '';
                $childSlug = '';
                foreach (explode('.', $slug) as $index => $part) {
                    if ($index === 0 && array_key_exists($part, $undotted)) {
                        $path = $childSlug = $part;

                        continue;
                    }

                    $childSlug .= ($childSlug ? '.' : '') . $part;
                    $path .= ($path ? '.children.' : '') . $part;

                    Arr::set(
                        $undotted,
                        $path,
                        [
                            'name' => Str::headline($part),
                            'slug' => $childSlug,
                        ],
                    );
                }
            } else {
                Arr::set($undotted, $slug, $mediaCollection);
            }
        }

        return $this->calculateTree($undotted);
    }

    public function getMediaModel(): string
    {
        return resolve_static(FluxMedia::class, 'class');
    }

    public function mediaFolders(): MorphToMany
    {
        return $this->morphToMany(MediaFolder::class, 'model', 'media_folder_model');
    }

    /**
     * @throws InvalidManipulation
     */
    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('preview')
            ->width(150)
            ->height(150)
            ->keepOriginalImageFormat()
            ->quality(80)
            ->optimize();

        $this->addMediaConversion('thumb')
            ->width(100)
            ->height(100)
            ->keepOriginalImageFormat()
            ->quality(80)
            ->optimize();

        $this->addMediaConversion('thumb_280x280')
            ->width(280)
            ->height(280)
            ->keepOriginalImageFormat()
            ->quality(80)
            ->optimize();

        $this->addMediaConversion('thumb_400x400')
            ->width(400)
            ->height(400)
            ->keepOriginalImageFormat()
            ->quality(80)
            ->optimize();

        $this->addMediaConversion('thumb_800x800')
            ->width(800)
            ->height(800)
            ->keepOriginalImageFormat()
            ->quality(80)
            ->optimize();
    }

    protected function calculateTree(array $mediaCollections): array
    {
        $node = [];
        foreach ($mediaCollections as $item) {
            $node[] = [
                'id' => ($id = data_get($item, 'id')) ?? Str::uuid()->toString(),
                'name' => data_get($item, 'name'),
                'is_static' => data_get($item, 'is_static') ?? false,
                'slug' => $slug = data_get($item, 'slug'),
                'children' => array_merge(
                    $this->calculateTree(data_get($item, 'children') ?? []),
                    resolve_static(FluxMedia::class, 'query')
                        ->when(
                            $id,
                            fn (Builder $query) => $query
                                ->where('model_type', morph_alias(MediaFolder::class))
                                ->where('model_id', $id),
                            fn (Builder $query) => $query
                                ->where('model_type', $this->getMorphClass())
                                ->where('model_id', $this->getKey())
                                ->where('collection_name', $slug)
                        )
                        ->orderBy('name', 'ASC')
                        ->get([
                            'id',
                            'name',
                            'file_name',
                            'collection_name',
                            'disk',
                            'size',
                            'mime_type',
                            'created_at',
                        ])
                        ->makeVisible(['name', 'collection_name'])
                        ->toArray()
                ),
            ];
        }

        return collect($node)
            ->sortBy('name')
            ->toArray();
    }
}
