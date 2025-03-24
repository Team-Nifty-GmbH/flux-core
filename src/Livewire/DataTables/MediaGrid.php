<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Actions\Media\DeleteMedia;
use FluxErp\Models\Media;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
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

    public function deleteMedia(Media $media): bool
    {
        try {
            DeleteMedia::make($media->toArray())
                ->checkPermission()
                ->validate()
                ->execute();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->loadData();

        return true;
    }

    protected function getLayout(): string
    {
        return 'tall-datatables::layouts.grid';
    }

    protected function itemToArray($item): array
    {
        /** @var Media $item */
        $itemArray = parent::itemToArray($item);
        $itemArray['url'] = $item->hasGeneratedConversion('thumb_400x400')
            ? $item->getUrl('thumb_400x400')
            : (
                Str::startsWith($item->mime_type, 'image/')
                    ? $item->getUrl()
                    : route('icons', ['name' => 'document'])
            );

        return $itemArray;
    }
}
