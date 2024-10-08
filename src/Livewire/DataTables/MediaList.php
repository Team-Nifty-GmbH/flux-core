<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\Media;
use Illuminate\Database\Eloquent\Builder;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;

class MediaList extends BaseDataTable
{
    protected string $model = Media::class;

    public array $enabledCols = [
        'file_name',
        'collection_name',
    ];

    public array $formatters = [
        'url' => 'image',
    ];

    protected function getBuilder(Builder $builder): Builder
    {
        return $builder->addSelect('model_type', 'disk', 'conversions_disk');
    }

    protected function itemToArray($item): array
    {
        $item->makeVisible('collection_name');

        $itemArray = parent::itemToArray($item);
        $itemArray['url'] = $item->getUrl('thumb');

        return $itemArray;
    }

    protected function getRowActions(): array
    {
        return [
            DataTableButton::make(icon: 'save')
                ->label(__('Download'))
                ->attributes([
                    'x-on:click' => '$wire.downloadMedia(record.id)',
                ]),
            DataTableButton::make(icon: 'eye')
                ->label(__('View'))
                ->href('record.url')
                ->attributes([
                    'target' => '_blank',
                    'x-bind:href' => 'record.url',
                ]),
            DataTableButton::make(icon: 'trash')
                ->color('negative')
                ->label(__('Delete'))
                ->attributes([
                    'wire:flux-confirm.icon.error' => __('Delete media') . '|' .
                        __('Do you really want to delete this media?') . '|' .
                        __('Cancel') . '|' .
                        __('Delete'),
                    'wire:click' => 'deleteMedia(record.id)',
                ]),
        ];
    }

    protected function getLeftAppends(): array
    {
        return [
            'name' => [
                'url',
            ],
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
