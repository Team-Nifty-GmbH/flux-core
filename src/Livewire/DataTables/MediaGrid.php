<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Actions\BlockedFile\BlockFile;
use FluxErp\Actions\Media\DeleteMedia;
use FluxErp\Models\Media;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Renderless;
use Spatie\Permission\Exceptions\UnauthorizedException;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;

class MediaGrid extends MediaList
{
    public array $enabledCols = [
        'url',
        'file_name',
    ];

    public array $formatters = [
        'url' => 'image',
    ];

    protected function getRowActions(): array
    {
        return array_merge(
            parent::getRowActions(),
            [
                DataTableButton::make()
                    ->icon('no-symbol')
                    ->color('red')
                    ->text(__('Block File'))
                    ->when(fn () => resolve_static(BlockFile::class, 'canPerformAction', [false]))
                    ->attributes([
                        'wire:flux-confirm.type.error' => __('Block this file? It will be deleted and never stored again.'),
                        'wire:click' => 'blockFile(record.id).then(() => show = false)',
                    ]),
            ]
        );
    }

    #[Renderless]
    public function blockFile(int $mediaId): bool
    {
        try {
            BlockFile::make(['media_id' => $mediaId])
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

    protected function augmentItemArray(array &$itemArray, Model $item): void
    {
        parent::augmentItemArray($itemArray, $item);

        /** @var Media $item */
        $itemArray['url'] = $item->hasGeneratedConversion('thumb_400x400')
            ? $item->getUrl('thumb_400x400')
            : (
                Str::startsWith((string) $item->mime_type, 'image/')
                    ? $item->getUrl()
                    : route('icons', ['name' => 'document'])
            );
    }
}
