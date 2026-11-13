<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\FixedAsset\CreateFixedAsset;
use FluxErp\Actions\FixedAsset\DeleteFixedAsset;
use FluxErp\Actions\FixedAsset\UpdateFixedAsset;
use Livewire\Attributes\Locked;

class FixedAssetForm extends FluxForm
{
    public ?string $acquired_at = null;

    public ?float $acquisition_cost = null;

    #[Locked]
    public ?float $book_value = null;

    public ?int $contact_id = null;

    public ?int $depreciation_ledger_account_id = null;

    public ?string $depreciation_method_enum = null;

    public ?string $depreciation_starts_at = null;

    public ?string $externally_depreciated_until = null;

    #[Locked]
    public ?int $id = null;

    public ?int $ledger_account_id = null;

    public ?string $name = null;

    public ?string $number = null;

    public ?int $order_id = null;

    public ?int $order_position_id = null;

    public ?float $residual_value = null;

    public ?int $tenant_id = null;

    public ?int $useful_life_months = null;

    protected function getActions(): array
    {
        return [
            'create' => CreateFixedAsset::class,
            'update' => UpdateFixedAsset::class,
            'delete' => DeleteFixedAsset::class,
        ];
    }
}
