<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\LeadLossReason\CreateLeadLossReason;
use FluxErp\Actions\LeadLossReason\DeleteLeadLossReason;
use FluxErp\Actions\LeadLossReason\UpdateLeadLossReason;
use FluxErp\Traits\Livewire\SupportsAutoRender;
use Livewire\Attributes\Locked;

class LeadLossReasonForm extends FluxForm
{
    use SupportsAutoRender;

    #[Locked]
    public ?int $id = null;

    public bool $is_active = true;

    public ?string $name = null;

    protected function getActions(): array
    {
        return [
            'create' => CreateLeadLossReason::class,
            'update' => UpdateLeadLossReason::class,
            'delete' => DeleteLeadLossReason::class,
        ];
    }

    protected function renderAsModal(): bool
    {
        return true;
    }
}
