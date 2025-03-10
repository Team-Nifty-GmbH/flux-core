<?php

namespace FluxErp\Livewire\Product\SerialNumber;

use FluxErp\Actions\SerialNumber\CreateSerialNumber;
use FluxErp\Livewire\DataTables\SerialNumberList as BaseSerialNumberList;
use FluxErp\Livewire\Forms\StockPostingForm;
use FluxErp\Models\Warehouse;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Exceptions\UnauthorizedException;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;

class SerialNumberList extends BaseSerialNumberList
{
    protected ?string $includeBefore = 'flux::livewire.product.serial-number.serial-number-list';

    public StockPostingForm $stockPosting;

    public function mount(): void
    {
        $this->stockPosting->address = [
            'id' => null,
            'quantity' => 1,
        ];

        parent::mount();
    }

    public function edit(): void
    {
        $this->stockPosting->reset();
        $this->stockPosting->address = [
            'id' => null,
            'quantity' => 1,
        ];

        $this->js(<<<'JS'
            $modalOpen('create-serial-number-modal');
        JS);
    }

    public function save(): bool
    {
        try {
            $this->stockPosting->warehouse_id = resolve_static(Warehouse::class, 'default')?->id;
            $this->stockPosting->order_position_id = null;
            $this->stockPosting->serial_number_id = null;
            $this->stockPosting->posting = 0;
            $this->stockPosting->purchase_price ??= 0;
            $this->stockPosting->save();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->loadData();

        return true;
    }

    protected function getTableActions(): array
    {
        return [
            DataTableButton::make()
                ->text(__('New'))
                ->icon('plus')
                ->color('indigo')
                ->wireClick('edit')
                ->when(resolve_static(CreateSerialNumber::class, 'canPerformAction', [false])),
        ];
    }
}
