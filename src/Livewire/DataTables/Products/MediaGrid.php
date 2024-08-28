<?php

namespace FluxErp\Livewire\DataTables\Products;

use FluxErp\Actions\Media\UploadMedia;
use FluxErp\Actions\Product\UpdateProduct;
use FluxErp\Livewire\DataTables\MediaGrid as BaseMediaGrid;
use FluxErp\Livewire\Forms\ProductForm;
use FluxErp\Models\Media;
use FluxErp\Models\Product;
use FluxErp\Traits\Livewire\WithFileUploads;
use Illuminate\Validation\ValidationException;
use Illuminate\View\ComponentAttributeBag;
use Livewire\Attributes\Js;
use Livewire\Attributes\Modelable;
use Spatie\Permission\Exceptions\UnauthorizedException;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;

class MediaGrid extends BaseMediaGrid
{
    use WithFileUploads;

    public array $enabledCols = [
        'url',
        'file_name',
    ];

    public array $formatters = [
        'url' => 'image',
    ];

    public array $uploads = [];

    #[Modelable]
    public ProductForm $product;

    public function mount(): void
    {
        parent::mount();

        $this->collection = 'images';
    }

    protected function getRowActions(): array
    {
        $rowActions = parent::getRowActions();
        array_splice($rowActions, -1, 0, [
            DataTableButton::make(icon: 'photograph')
                ->label(__('Cover image'))
                ->attributes([
                    'x-on:click' => '$wire.product.cover_media_id = record.id; edit = true;',
                    'x-show' => 'record.id !== $wire.product.cover_media_id',
                ])
                ->when(fn () => resolve_static(UpdateProduct::class, 'canPerformAction', [false])),
        ]);

        return $rowActions;
    }

    protected function getTableActions(): array
    {
        return [
            DataTableButton::make(icon: 'upload')
                ->color('primary')
                ->label(__('Upload'))
                ->attributes([
                    'wire:click' => 'uploadMedia()',
                ])
                ->when(fn () => resolve_static(UploadMedia::class, 'canPerformAction', [false])),
        ];
    }

    protected function getRowAttributes(): ComponentAttributeBag
    {
        return new ComponentAttributeBag(
            [
                'x-bind:class' => <<<'JS'
                    record.id === $wire.product.cover_media_id ? 'bg-primary-50 ring-2 ring-primary-500 ring-offset-2' : ''
                JS
            ]
        );
    }

    #[Js]
    public function uploadMedia(): string
    {
        return <<<'JS'
            let input = document.createElement('input');
            input.type = 'file';
            input.multiple = true;
            input.click();

            input.onchange = e => {
                $wire.uploadMultiple('uploads', e.target.files);
            }
        JS;
    }

    public function updatedUploads(): void
    {
        foreach ($this->uploads as $index => $file) {
            try {
                $media = UploadMedia::make([
                    'name' => $file->getClientOriginalName(),
                    'file_name' => $file->getClientOriginalName(),
                    'model_type' => morph_alias(Product::class),
                    'model_id' => $this->product->id,
                    'media' => $file,
                    'collection_name' => $this->collection,
                ])
                    ->checkPermission()
                    ->validate()
                    ->execute();
                if (! $this->product->cover_media_id && $index === 0) {
                    UpdateProduct::make([
                        'id' => $this->product->id,
                        'cover_media_id' => $media->id,
                    ])
                        ->validate()
                        ->execute();
                    $this->product->cover_media_id = $media->id;
                }
            } catch (ValidationException|UnauthorizedException $e) {
                exception_to_notifications($e, $this);
            }
        }

        $this->loadData();
    }

    public function deleteMedia(Media $media): bool
    {
        $delete = parent::deleteMedia($media);

        if ($delete) {
            if ($this->product->cover_media_id === $media->id) {
                $this->product->cover_media_id = resolve_static(Media::class, 'query')
                    ->where('model_id', $this->product->id)
                    ->where('model_type', morph_alias(Product::class))
                    ->where('collection_name', 'images')
                    ->value('id');
            }

            try {
                UpdateProduct::make([
                    'id' => $this->product->id,
                    'cover_media_id' => $this->product->cover_media_id,
                ])
                    ->validate()
                    ->execute();
            } catch (ValidationException|UnauthorizedException $e) {
                exception_to_notifications($e, $this);
            }
        }

        return $delete;
    }
}
