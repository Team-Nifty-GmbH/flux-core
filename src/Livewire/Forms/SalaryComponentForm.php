<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\SalaryComponent\CreateSalaryComponent;
use FluxErp\Actions\SalaryComponent\DeleteSalaryComponent;
use FluxErp\Actions\SalaryComponent\UpdateSalaryComponent;
use FluxErp\Traits\Livewire\Form\SupportsAutoRender;
use Livewire\Attributes\Locked;

class SalaryComponentForm extends FluxForm
{
    use SupportsAutoRender;

    public ?string $amount = null;

    public ?int $employee_id = null;

    #[Locked]
    public ?int $id = null;

    public ?string $name = null;

    public ?string $payment_interval = null;

    public ?string $valid_from = null;

    public ?string $valid_until = null;

    protected function getActions(): array
    {
        return [
            'create' => CreateSalaryComponent::class,
            'update' => UpdateSalaryComponent::class,
            'delete' => DeleteSalaryComponent::class,
        ];
    }
}
