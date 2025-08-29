<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\AbsenceRequest\ApproveAbsenceRequest;
use FluxErp\Actions\AbsenceRequest\CreateAbsenceRequest;
use FluxErp\Actions\AbsenceRequest\DeleteAbsenceRequest;
use FluxErp\Actions\AbsenceRequest\RejectAbsenceRequest;
use FluxErp\Actions\AbsenceRequest\RevokeAbsenceRequest;
use FluxErp\Actions\AbsenceRequest\UpdateAbsenceRequest;
use FluxErp\Actions\FluxAction;
use FluxErp\Enums\AbsenceRequestCreationTypeEnum;
use FluxErp\Enums\AbsenceRequestStatusEnum;
use FluxErp\Models\AbsenceRequest;
use FluxErp\Models\AbsenceType;
use FluxErp\Traits\Livewire\SupportsAutoRender;
use Livewire\Attributes\Locked;

class AbsenceRequestForm extends FluxForm
{
    use SupportsAutoRender;

    #[Locked]
    public ?array $absence_type = null;

    public ?int $absence_type_id = null;

    #[Locked]
    public ?string $approved_at = null;

    #[Locked]
    public ?int $approved_by_id = null;

    public ?string $comment = null;

    public ?float $days_requested = null;

    public ?array $employee = null;

    public ?int $employee_id = null;

    public ?string $end_date = null;

    #[Locked]
    public ?int $id = null;

    public ?bool $is_emergency = false;

    public ?string $reason = null;

    #[Locked]
    public ?string $rejected_at = null;

    #[Locked]
    public ?int $rejected_by_id = null;

    public ?string $sick_note_issued_date = null;

    public ?string $start_date = null;

    public ?string $status = 'pending';

    public ?int $substitute_employee_id = null;

    public ?string $substitute_note = null;

    #[Locked]
    public ?string $updated_at = null;

    public function changeState(AbsenceRequestStatusEnum $status): void
    {
        $action = $this->makeAction($status->value)
            ->when($this->checkPermission, fn (FluxAction $action) => $action->checkPermission())
            ->validate();

        $response = $action->execute();

        $this->actionResult = $response;

        $this->fill($response);
    }

    public function fill($values): void
    {
        if ($values instanceof AbsenceRequest) {
            $values->loadMissing([
                'absenceType:id,name',
                'employee:id,name',
            ]);
        }

        parent::fill($values);
    }

    public function save(): void
    {
        if (
            method_exists($this->getComponent(), 'canChooseEmployee')
            && ! $this->getComponent()->canChooseEmployee()
        ) {
            $this->employee_id = $this->component->employeeId;

            if (
                resolve_static(AbsenceType::class, 'query')
                    ->whereKey($this->absence_type_id)
                    ->first()
                    ?->employee_can_create === AbsenceRequestCreationTypeEnum::Yes
            ) {
                $this->status = AbsenceRequestStatusEnum::Approved->value;
                $this->approved_at = now();
                $this->approved_by_id = auth()->id();
            }
        }

        parent::save();
    }

    protected function getActions(): array
    {
        return [
            'create' => CreateAbsenceRequest::class,
            'update' => UpdateAbsenceRequest::class,
            'delete' => DeleteAbsenceRequest::class,
            'revoked' => RevokeAbsenceRequest::class,
            'approved' => ApproveAbsenceRequest::class,
            'rejected' => RejectAbsenceRequest::class,
        ];
    }
}
