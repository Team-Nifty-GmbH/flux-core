<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Actions\Media\DeleteMedia;
use FluxErp\Livewire\Forms\MediaForm;
use FluxErp\Models\Media;
use FluxErp\Traits\Livewire\Actions;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Renderless;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;

class MediaList extends BaseDataTable
{
    use Actions;

    public array $enabledCols = [
        'file_name',
        'collection_name',
    ];

    public array $formatters = [
        'url' => 'image',
    ];

    public ?string $includeBefore = 'flux::livewire.datatables.media-list';

    public MediaForm $mediaForm;

    protected string $model = Media::class;

    protected function getRowActions(): array
    {
        return [
            DataTableButton::make()
                ->icon('arrow-down-tray')
                ->text(__('Download'))
                ->attributes([
                    'x-on:click' => 'show = false; $wire.downloadMedia(record.id)',
                ]),
            DataTableButton::make()
                ->icon('pencil')
                ->text(__('Edit'))
                ->wireClick('edit(record.id); show = false;'),
            DataTableButton::make()
                ->icon('eye')
                ->text(__('View'))
                ->href('record.url')
                ->attributes([
                    'target' => '_blank',
                    'x-bind:href' => 'record.url',
                ]),
            DataTableButton::make()
                ->icon('trash')
                ->color('red')
                ->text(__('Delete'))
                ->when(fn () => resolve_static(DeleteMedia::class, 'canPerformAction', [false]))
                ->attributes([
                    'wire:flux-confirm.type.error' => __('wire:confirm.delete', ['model' => __('Media')]),
                    'wire:click' => 'deleteMedia(record.id).then(() => show = false)',
                ]),
        ];
    }

    public function downloadMedia(Media $media): false|BinaryFileResponse
    {
        if (! file_exists($media->getPath())) {
            $this->notification()->error(__('The file does not exist anymore.'))->send();

            return false;
        }

        return response()->download($media->getPath(), $media->file_name);
    }

    #[Renderless]
    public function edit(Media $media): void
    {
        $media->makeVisible([
            'id',
            'name',
            'file_name',
            'collection_name',
            'disk',
            'size',
            'mime_type',
            'created_at',
        ]);
        $this->mediaForm->fill($media);

        $this->js(<<<'JS'
            $modalOpen('edit-media');
        JS);
    }

    public function save(): bool
    {
        try {
            $this->mediaForm->save();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->loadData();

        return true;
    }

    protected function getBuilder(Builder $builder): Builder
    {
        return $builder->addSelect('model_type', 'disk', 'conversions_disk');
    }

    protected function getLeftAppends(): array
    {
        return [
            'name' => [
                'url',
            ],
        ];
    }

    protected function itemToArray($item): array
    {
        /** @var Media $item */
        $item->makeVisible('collection_name');

        $itemArray = parent::itemToArray($item);
        $itemArray['url'] = $item->hasGeneratedConversion('thumb')
            ? $item->getUrl('thumb')
            : (
                Str::startsWith($item->mime_type, 'image/')
                    ? $item->getUrl('thumb')
                    : route('icons', ['name' => 'document'])
            );

        return $itemArray;
    }
}
