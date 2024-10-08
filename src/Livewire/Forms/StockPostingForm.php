<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\StockPosting\CreateStockPosting;
use FluxErp\Actions\StockPosting\DeleteStockPosting;
use FluxErp\Actions\StockPosting\UpdateStockPosting;
use Livewire\Attributes\Locked;

class StockPostingForm extends FluxForm
{
    #[Locked]
    public ?int $id = null;

    public ?int $warehouse_id = null;

    public ?int $product_id = null;

    public ?int $order_position_id = null;

    public ?int $serial_number_id = null;

    public ?string $remaining_stock = null;

    public ?string $reserved_stock = null;

    public ?string $posting = null;

    public ?string $purchase_price = null;

    public ?string $description = null;

    public array $serial_number = [
        'serial_number_range_id' => null,
        'serial_number' => null,
        'supplier_serial_number' => null,
        'use_supplier_serial_number' => false,
    ];

    public array $address = [];

    protected function getActions(): array
    {
        return [
            'create' => CreateStockPosting::class,
            'update' => UpdateStockPosting::class,
            'delete' => DeleteStockPosting::class,
        ];
    }
}