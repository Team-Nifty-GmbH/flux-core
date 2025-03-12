<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\SerialNumberRange\CreateSerialNumberRange;
use FluxErp\Actions\SerialNumberRange\DeleteSerialNumberRange;
use FluxErp\Actions\SerialNumberRange\UpdateSerialNumberRange;
use Livewire\Attributes\Locked;

class SerialNumberRangeForm extends FluxForm
{
    public ?int $client_id = null;

    public ?int $current_number = 0;

    public ?string $description = null;

    #[Locked]
    public ?int $id = null;

    public bool $is_pre_filled = false;

    public ?int $length = 4;

    public ?string $model_type = null;

    public ?string $prefix = null;

    public bool $stores_serial_numbers = false;

    public ?string $suffix = null;

    public ?string $type = null;

    protected function getActions(): array
    {
        return [
            'create' => CreateSerialNumberRange::class,
            'update' => UpdateSerialNumberRange::class,
            'delete' => DeleteSerialNumberRange::class,
        ];
    }
}
