<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Actions\Media\DeleteMedia;
use FluxErp\Models\Media;
use Illuminate\Validation\ValidationException;
use Spatie\MediaLibrary\MediaCollections\Exceptions\InvalidConversion;
use Spatie\Permission\Exceptions\UnauthorizedException;

class MediaGrid extends MediaList
{
    public array $enabledCols = [
        'url',
        'file_name',
    ];

    public array $formatters = [
        'url' => 'image',
    ];

    protected function getLayout(): string
    {
        return 'tall-datatables::layouts.grid';
    }

    protected function itemToArray($item): array
    {
        $itemArray = parent::itemToArray($item);
        try {
            $itemArray['url'] = $item->getUrl('thumb_400x400');
        } catch (InvalidConversion) {
            $itemArray['url'] = $item->getUrl();
        }

        return $itemArray;
    }

    public function deleteMedia(Media $media): void
    {
        try {
            DeleteMedia::make($media->toArray())
                ->checkPermission()
                ->validate()
                ->execute();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return;
        }

        $this->loadData();
    }
}
