<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\SerialNumberRange\CreateSerialNumberRange;
use FluxErp\Actions\SerialNumberRange\UpdateSerialNumberRange;
use Livewire\Attributes\Locked;
use Livewire\Form;

class SerialNumberRangeForm extends Form
{
    #[Locked]
    public ?int $id = null;

    public ?int $client_id = null;

    public ?string $model_type = null;

    public ?string $type = null;

    public ?int $current_number = 0;

    public ?string $prefix = null;

    public ?string $suffix = null;

    public ?string $description = null;

    public ?int $length = 4;

    public bool $is_pre_filled = false;

    public bool $stores_serial_numbers = false;

    public function save(): void
    {
        $action = $this->id
            ? UpdateSerialNumberRange::make($this->toArray())
            : CreateSerialNumberRange::make($this->toArray());

        $response = $action->validate()->execute();

        $this->fill($response);
    }
}
