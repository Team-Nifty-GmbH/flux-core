<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\StockPosting\TransferStock;

class StockTransferForm extends FluxForm
{
    public ?string $amount = null;

    public ?string $description = null;

    public ?int $from_warehouse_bin_id = null;

    public ?int $lot_id = null;

    public ?int $product_id = null;

    public ?int $to_warehouse_bin_id = null;

    public ?int $warehouse_id = null;

    protected function getActions(): array
    {
        return [
            'create' => TransferStock::class,
        ];
    }
}
