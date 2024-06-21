<?php

namespace FluxErp\Traits;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Spatie\Image\Exceptions\InvalidManipulation;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

trait InteractsWithMedia
{
    use \Spatie\MediaLibrary\InteractsWithMedia;

    public function getMediaAsTree(): array
    {
        $mediaCollections = array_unique(
            array_merge(
                $registeredMediaCollections = $this->getRegisteredMediaCollections()
                    ->pluck('name')
                    ->toArray(),
                $this->media()
                    ->groupBy('collection_name')
                    ->select('collection_name')
                    ->pluck('collection_name')
                    ->toArray()
            )
        );

        sort($mediaCollections);

        $mediaCollections = Arr::undot(array_flip($mediaCollections));

        foreach ($registeredMediaCollections as $collection) {
            data_set($mediaCollections, $collection . '.is_static', true);
        }

        return $this->calculateTree($mediaCollections);
    }

    private function calculateTree(array $mediaCollections, ?string $prefix = null): array
    {
        $node = [];
        foreach ($mediaCollections as $key => $item) {
            $isStatic = $item['is_static'] ?? false;
            if (is_array($item)) {
                unset($item['is_static']);
            }

            $node[] = [
                'name' => $key,
                'id' => Str::uuid()->toString(),
                'is_static' => $isStatic,
                'collection_name' => $prefix . $key,
                'children' => is_array($item) ?
                    array_merge(
                        $this->calculateTree($item, $prefix . $key . '.'),
                        $this->media()
                            ->where('collection_name', $prefix . $key)
                            ->orderBy('name', 'ASC')
                            ->get()
                            ->makeVisible(['id', 'name', 'file_name', 'collection_name', 'disk', 'size', 'mime_type', 'created_at'])
                            ->toArray(),
                    ) :
                    $this->media()
                        ->where('collection_name', $prefix . $key)
                        ->orderBy('name', 'ASC')
                        ->get(['id', 'name', 'file_name', 'collection_name', 'disk', 'size', 'mime_type', 'created_at'])
                        ->makeVisible(['name', 'collection_name'])
                        ->toArray(),
            ];
        }

        return $node;
    }

    /**
     * @throws InvalidManipulation
     */
    public function registerMediaConversions(?Media $media = null): void
    {
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
}
