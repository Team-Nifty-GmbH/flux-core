<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\AbsenceRequest\CreateAbsenceRequest;
use FluxErp\Actions\AbsenceRequest\DeleteAbsenceRequest;
use FluxErp\Actions\AbsenceRequest\UpdateAbsenceRequest;
use FluxErp\Models\AbsenceRequest;
use FluxErp\Traits\Livewire\SupportsAutoRender;
use Livewire\Attributes\Locked;

class AbsenceRequestForm extends FluxForm
{
    use SupportsAutoRender;
    
    #[Locked]
    public ?int $id = null;
    
    public ?int $user_id = null;
    
    public ?int $work_time_category_id = null;
    
    public ?string $start_date = null;
    
    public ?string $end_date = null;
    
    public ?string $start_half_day = 'full';
    
    public ?string $end_half_day = 'full';
    
    public ?int $substitute_user_id = null;
    
    public ?string $reason = null;
    
    public ?bool $is_emergency = false;
    
    public ?string $status = 'draft';
    
    public ?float $days_requested = null;
    
    public ?int $approved_by = null;
    
    public ?string $approved_at = null;
    
    public ?string $approval_note = null;
    
    public ?string $rejection_reason = null;

    protected function getActions(): array
    {
        return [
            'create' => CreateAbsenceRequest::class,
            'update' => UpdateAbsenceRequest::class,
            'delete' => DeleteAbsenceRequest::class,
        ];
    }

    protected static function getModel(): string
    {
        return AbsenceRequest::class;
    }
}