<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\Lot\CreateLot;
use FluxErp\Actions\Lot\DeleteLot;
use FluxErp\Actions\Lot\UpdateLot;
use Livewire\Attributes\Locked;

class LotForm extends FluxForm
{
    public ?string $blocked_at = null;

    public ?string $description = null;

    public ?string $expires_at = null;

    #[Locked]
    public ?int $id = null;

    public ?string $lot_number = null;

    public ?string $produced_at = null;

    public ?int $product_id = null;

    public ?string $supplier_lot_number = null;

    protected function getActions(): array
    {
        return [
            'create' => CreateLot::class,
            'update' => UpdateLot::class,
            'delete' => DeleteLot::class,
        ];
    }
}
