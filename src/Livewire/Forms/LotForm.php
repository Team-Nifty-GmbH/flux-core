<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\Lot\CreateLot;
use FluxErp\Actions\Lot\DeleteLot;
use FluxErp\Actions\Lot\UpdateLot;
use FluxErp\Models\Lot;
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

    public function fill($values): void
    {
        parent::fill($values);

        // The datetime-local input only accepts this format and shows the serialized date empty.
        if ($values instanceof Lot) {
            $this->blocked_at = $values->blocked_at?->format('Y-m-d\TH:i');
        }
    }

    protected function getActions(): array
    {
        return [
            'create' => CreateLot::class,
            'update' => UpdateLot::class,
            'delete' => DeleteLot::class,
        ];
    }
}
