<?php

namespace FluxErp\Livewire\Product;

use FluxErp\Actions\Lot\CreateLot;
use FluxErp\Actions\Lot\DeleteLot;
use FluxErp\Actions\Lot\UpdateLot;
use FluxErp\Livewire\DataTables\LotList as BaseLotList;
use FluxErp\Livewire\Forms\LotForm;
use FluxErp\Livewire\Forms\ProductForm;
use FluxErp\Models\Lot;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Modelable;
use Livewire\Attributes\Renderless;
use Spatie\Permission\Exceptions\UnauthorizedException;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;

class LotList extends BaseLotList
{
    public array $enabledCols = [
        'lot_number',
        'supplier_lot_number',
        'produced_at',
        'expires_at',
        'blocked_at',
    ];

    public LotForm $lot;

    #[Modelable]
    public ProductForm $product;

    protected ?string $includeBefore = 'flux::livewire.product.lot-list';

    protected function getTableActions(): array
    {
        return [
            DataTableButton::make()
                ->text(__('New'))
                ->icon('plus')
                ->color('indigo')
                ->when(resolve_static(CreateLot::class, 'canPerformAction', [false]))
                ->attributes([
                    'wire:click' => 'edit()',
                ]),
        ];
    }

    protected function getRowActions(): array
    {
        return [
            DataTableButton::make()
                ->text(__('Edit'))
                ->icon('pencil')
                ->color('indigo')
                ->when(resolve_static(UpdateLot::class, 'canPerformAction', [false]))
                ->attributes([
                    'wire:click' => 'edit(record.id)',
                ]),
            DataTableButton::make()
                ->text(__('Delete'))
                ->color('red')
                ->icon('trash')
                ->when(resolve_static(DeleteLot::class, 'canPerformAction', [false]))
                ->attributes([
                    'wire:click' => 'delete(record.id)',
                    'wire:flux-confirm.type.error' => __('wire:confirm.delete', ['model' => __('Lot')]),
                ]),
        ];
    }

    public function delete(Lot $lot): bool
    {
        $this->lot->reset();
        $this->lot->fill($lot);

        try {
            $this->lot->delete();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->loadData();

        return true;
    }

    #[Renderless]
    public function edit(Lot $lot): void
    {
        $this->lot->reset();
        $this->lot->fill($lot);

        $this->modalOpen('edit-lot-modal');
    }

    public function save(): bool
    {
        $this->lot->product_id = $this->product->id;

        try {
            $this->lot->save();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->loadData();

        return true;
    }

    protected function getBuilder(Builder $builder): Builder
    {
        return $builder->where('product_id', $this->product->id);
    }
}
