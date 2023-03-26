<?php

namespace FluxErp\Traits;

use Illuminate\Support\Arr;

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
                'is_static' => $isStatic,
                'collection_name' => $prefix . $key,
                'children' => is_array($item) ?
                    array_merge(
                        $this->calculateTree($item, $prefix . $key . '.'),
                        $this->media()
                            ->where('collection_name', $prefix . $key)
                            ->orderBy('name', 'ASC')
                            ->get(['id', 'name', 'collection_name', 'disk'])
                            ->makeVisible(['id', 'name', 'collection_name', 'disk'])
                            ->toArray(),
                    ) :
                    $this->media()
                        ->where('collection_name', $prefix . $key)
                        ->orderBy('name', 'ASC')
                        ->get(['id', 'name', 'collection_name', 'disk'])
                        ->makeVisible(['name', 'collection_name'])
                        ->toArray(),
            ];
        }

        return $node;
    }
}
