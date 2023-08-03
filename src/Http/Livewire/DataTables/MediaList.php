<?php

namespace FluxErp\Http\Livewire\DataTables;

use FluxErp\Models\Media;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use TeamNiftyGmbH\DataTable\DataTable;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;

class MediaList extends DataTable
{
    protected string $model = Media::class;

    public array $enabledCols = [
        'file_name',
        'collection_name',
    ];

    public function itemToArray($item): array
    {
        $item->makeVisible('collection_name');

        $itemArray = parent::itemToArray($item);
        $itemArray['url'] = $item->getUrl();

        return $itemArray;
    }

    public function getRowActions(): array
    {
        return [
            DataTableButton::make(icon: 'save')
                ->attributes([
                    'x-on:click' => '$wire.downloadMedia(record.id)',
                ]),
            DataTableButton::make(icon: 'eye')
                ->href('record.url')
                ->attributes([
                    'target' => '_blank',
                    'x-bind:href' => 'record.url',
                ]),
        ];
    }

    public function downloadMedia(Media $media): false|BinaryFileResponse
    {
        if (! file_exists($media->getPath())) {
            $this->notification()->error(__('The file does not exist anymore.'));

            return false;
        }

        return response()->download($media->getPath(), $media->file_name);
    }
}
