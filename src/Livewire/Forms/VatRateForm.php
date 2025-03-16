<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\VatRate\CreateVatRate;
use FluxErp\Actions\VatRate\DeleteVatRate;
use FluxErp\Actions\VatRate\UpdateVatRate;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Validate;

class VatRateForm extends FluxForm
{
    public ?string $footer_text = null;

    #[Locked]
    public ?int $id = null;

    public bool $is_default = false;

    public bool $is_tax_exemption = false;

    public ?string $name = null;

    public ?float $rate_percentage = null;

    #[Validate(['required', 'numeric', 'min:0', 'max:99.99'])]
    public ?float $rate_percentage_frontend = null;

    public function fill($values): void
    {
        parent::fill($values);

        $this->rate_percentage_frontend = bcmul($this->rate_percentage, 100);
    }

    public function save(): void
    {
        $this->rate_percentage = bcdiv($this->rate_percentage_frontend, 100);

        parent::save();
    }

    protected function getActions(): array
    {
        return [
            'create' => CreateVatRate::class,
            'update' => UpdateVatRate::class,
            'delete' => DeleteVatRate::class,
        ];
    }
}
