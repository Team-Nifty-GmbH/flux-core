<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\LeadState\CreateLeadState;
use FluxErp\Actions\LeadState\DeleteLeadState;
use FluxErp\Actions\LeadState\UpdateLeadState;
use FluxErp\Traits\Livewire\SupportsAutoRender;
use Livewire\Attributes\Locked;

class LeadStateForm extends FluxForm
{
    use SupportsAutoRender;

    public ?string $color = null;

    #[Locked]
    public ?int $id = null;

    public bool $is_default = false;

    public bool $is_loss = false;

    public bool $is_win = false;

    public ?string $name = null;

    public float|int|string|null $probability_percentage = null;

    public function fill($values): void
    {
        parent::fill($values);

        $this->probability_percentage = ! is_null($this->probability_percentage)
            ? bcmul($this->probability_percentage, 100)
            : null;
    }

    public function toActionData(): array
    {
        $data = parent::toActionData();
        $data['probability_percentage'] = ! is_null($this->probability_percentage)
            ? bcdiv($this->probability_percentage, 100)
            : null;

        return $data;
    }

    protected function getActions(): array
    {
        return [
            'create' => CreateLeadState::class,
            'update' => UpdateLeadState::class,
            'delete' => DeleteLeadState::class,
        ];
    }
}
