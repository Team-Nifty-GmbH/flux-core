<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\AbsencePolicy\CreateAbsencePolicy;
use FluxErp\Actions\AbsencePolicy\DeleteAbsencePolicy;
use FluxErp\Actions\AbsencePolicy\UpdateAbsencePolicy;
use FluxErp\Traits\Livewire\SupportsAutoRender;
use Livewire\Attributes\Locked;

class AbsencePolicyForm extends FluxForm
{
    use SupportsAutoRender;

    public bool $can_select_substitute = false;

    public ?int $documentation_after_days = null;

    #[Locked]
    public ?int $id = null;

    public bool $is_active = true;

    public ?int $max_consecutive_days = null;

    public ?int $min_notice_days = null;

    public ?string $name = null;

    public bool $requires_documentation = false;

    public bool $requires_reason = false;

    public bool $requires_substitute = false;

    protected function getActions(): array
    {
        return [
            'create' => CreateAbsencePolicy::class,
            'update' => UpdateAbsencePolicy::class,
            'delete' => DeleteAbsencePolicy::class,
        ];
    }
}
