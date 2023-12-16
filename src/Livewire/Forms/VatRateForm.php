<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\VatRate\CreateVatRate;
use FluxErp\Actions\VatRate\UpdateVatRate;
use Livewire\Attributes\Locked;
use Livewire\Form;

class VatRateForm extends Form
{
    #[Locked]
    public ?int $id = null;

    public ?string $name = null;

    public ?float $rate_percentage = null;

    public ?string $footer_text = null;

    public ?float $rate_percentage_frontend = null;

    public function save(): void
    {
        $this->rate_percentage = bcdiv($this->rate_percentage_frontend, 100);
        $action = $this->id ? UpdateVatRate::make($this->toArray()) : CreateVatRate::make($this->toArray());

        $response = $action->validate()->execute();

        $this->fill($response);
    }

    public function fill($values): void
    {
        parent::fill($values);

        $this->rate_percentage_frontend = bcmul($this->rate_percentage, 100);
    }
}
