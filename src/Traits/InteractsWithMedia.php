<?php

namespace FluxErp\Traits;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

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

    private function calculateTree(array $mediaCollections, string $prefix = null): array
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
                        ->get(['id', 'name', 'file_name', 'collection_name', 'disk', 'mime_type', 'size', 'created_at'])
                        ->makeVisible(['name', 'collection_name'])
                        ->toArray(),
            ];
        }

        return $node;
    }
}
