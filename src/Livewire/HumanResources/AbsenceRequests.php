<?php

namespace FluxErp\Livewire\HumanResources;

use FluxErp\Actions\AbsenceRequest\ApproveAbsenceRequest;
use FluxErp\Actions\AbsenceRequest\RejectAbsenceRequest;
use FluxErp\Livewire\DataTables\AbsenceRequestList;
use FluxErp\Livewire\Forms\AbsenceRequestForm;
use FluxErp\Models\AbsenceRequest;
use FluxErp\Support\Livewire\Attributes\DataTableForm;
use FluxErp\Traits\Livewire\DataTableHasFormEdit;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Renderless;
use Spatie\Permission\Exceptions\UnauthorizedException;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;

class AbsenceRequests extends AbsenceRequestList
{
    use DataTableHasFormEdit;

    #[DataTableForm]
    public AbsenceRequestForm $absenceRequestForm;

    public ?string $viewType = 'my';

    public ?string $approvalNote = null;

    public ?string $rejectionReason = null;
    
    public ?AbsenceRequest $viewingRequest = null;

    protected ?string $includeBefore = 'flux::livewire.human-resources.absence-requests';

    protected function getTableActions(): array
    {
        return [
            DataTableButton::make()
                ->text(__('New Request'))
                ->color('indigo')
                ->icon('plus')
                ->wireClick('edit'),
        ];
    }

    protected function getRowActions(): array
    {
        return [
            DataTableButton::make()
                ->text(__('View'))
                ->icon('eye')
                ->color('gray')
                ->wireClick('view(record.id)'),

            DataTableButton::make()
                ->text(__('Edit'))
                ->icon('pencil')
                ->color('indigo')
                ->wireClick('edit(record.id)'),

            DataTableButton::make()
                ->text(__('Submit'))
                ->icon('arrow-up-circle')
                ->color('blue')
                ->wireClick('submit(record.id)'),

            DataTableButton::make()
                ->text(__('Approve'))
                ->icon('check-circle')
                ->color('green')
                ->wireClick('openApprovalModal(record.id)'),

            DataTableButton::make()
                ->text(__('Reject'))
                ->icon('x-circle')
                ->color('red')
                ->wireClick('openRejectionModal(record.id)'),

            DataTableButton::make()
                ->text(__('Cancel'))
                ->icon('x-mark')
                ->color('gray')
                ->wireClick('cancel(record.id)'),

            DataTableButton::make()
                ->text(__('Delete'))
                ->icon('trash')
                ->color('red')
                ->attributes([
                    'wire:click' => 'delete(record.id)',
                    'wire:flux-confirm.type.error' => __('wire:confirm.delete', ['model' => __('Vacation Request')]),
                ]),
        ];
    }


    #[Computed]
    public function vacationBalance(): array
    {
        $user = Auth::user();

        if (! $user->work_time_model_id) {
            return [
                'total' => 0,
                'used' => 0,
                'remaining' => 0,
                'pending' => 0,
            ];
        }

        $usedDays = resolve_static(AbsenceRequest::class, 'query')
            ->where('user_id', $user->id)
            ->where('status', 'approved')
            ->whereYear('start_date', now()->year)
            ->sum('days_requested');

        $pendingDays = resolve_static(AbsenceRequest::class, 'query')
            ->where('user_id', $user->id)
            ->where('status', 'pending')
            ->whereYear('start_date', now()->year)
            ->sum('days_requested');

        $totalDays = $user->workTimeModel->vacation_days ?? 0;

        return [
            'total' => $totalDays,
            'used' => $usedDays,
            'remaining' => $totalDays - $usedDays,
            'pending' => $pendingDays,
        ];
    }

    public function mount(): void
    {
        parent::mount();

        $this->applyViewFilter();
    }

    public function updatedViewType(): void
    {
        $this->applyViewFilter();
        $this->loadData();
    }

    #[Renderless]
    public function applyViewFilter(): void
    {
        $this->filters = [];

        switch ($this->viewType) {
            case 'my':
                $this->filters[] = [
                    'column' => 'user_id',
                    'operator' => '=',
                    'value' => Auth::id(),
                ];
                break;

            case 'team':
                $subordinates = Auth::user()->subordinates->pluck('id')->toArray();
                if (! empty($subordinates)) {
                    $this->filters[] = [
                        'column' => 'user_id',
                        'operator' => 'in',
                        'value' => $subordinates,
                    ];
                }
                break;

            case 'approval':
                $this->filters[] = [
                    'column' => 'status',
                    'operator' => '=',
                    'value' => 'pending',
                ];
                break;
        }
    }


    #[Renderless]
    public function edit(AbsenceRequest $absenceRequest): void
    {
        $this->absenceRequestForm->reset();
        $this->absenceRequestForm->fill($absenceRequest);

        $this->js(<<<'JS'
            $modalOpen('edit-absence-request-modal');
        JS);
    }

    #[Renderless]
    public function view(AbsenceRequest $absenceRequest): void
    {
        $this->absenceRequestForm->reset();
        $this->absenceRequestForm->fill($absenceRequest);
        $this->viewingRequest = $absenceRequest->load(['user', 'substituteUser', 'approvedBy']);

        $this->js(<<<'JS'
            $modalOpen('view-absence-request-modal');
        JS);
    }

    #[Renderless]
    public function save(): bool
    {
        $this->absenceRequestForm->user_id = $this->absenceRequestForm->user_id ?? Auth::id();

        try {
            $this->absenceRequestForm->save();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->loadData();

        return true;
    }

    #[Renderless]
    public function submit(AbsenceRequest $absenceRequest): void
    {
        if (! $absenceRequest->submit()) {
            $this->notification()->error(__('Unable to submit vacation request'));

            return;
        }

        $this->notification()->success(__('Vacation request submitted'));
        $this->loadData();
    }

    #[Renderless]
    public function cancel(AbsenceRequest $absenceRequest): void
    {
        if (! $absenceRequest->cancel()) {
            $this->notification()->error(__('Unable to cancel vacation request'));

            return;
        }

        $this->notification()->success(__('Vacation request cancelled'));
        $this->loadData();
    }

    #[Renderless]
    public function delete(AbsenceRequest $absenceRequest): void
    {
        $this->absenceRequestForm->reset();
        $this->absenceRequestForm->fill($absenceRequest);

        try {
            $this->absenceRequestForm->delete();
        } catch (UnauthorizedException|ValidationException $e) {
            exception_to_notifications($e, $this);

            return;
        }

        $this->loadData();
    }

    #[Renderless]
    public function openApprovalModal(AbsenceRequest $absenceRequest): void
    {
        $this->absenceRequestForm->reset();
        $this->absenceRequestForm->fill($absenceRequest);
        $this->approvalNote = null;

        $this->js(<<<'JS'
            $modalOpen('approve-absence-request-modal');
        JS);
    }

    #[Renderless]
    public function openRejectionModal(AbsenceRequest $absenceRequest): void
    {
        $this->absenceRequestForm->reset();
        $this->absenceRequestForm->fill($absenceRequest);
        $this->rejectionReason = null;

        $this->js(<<<'JS'
            $modalOpen('reject-absence-request-modal');
        JS);
    }

    #[Renderless]
    public function approve(): bool
    {
        try {
            ApproveAbsenceRequest::make([
                'id' => $this->absenceRequestForm->id,
                'approval_note' => $this->approvalNote,
            ])
                ->checkPermission()
                ->validate()
                ->execute();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->notification()->success(__('Vacation request approved'));
        $this->loadData();

        return true;
    }

    #[Renderless]
    public function reject(): bool
    {
        if (! $this->rejectionReason) {
            $this->notification()->error(__('Rejection reason is required'));

            return false;
        }

        try {
            RejectAbsenceRequest::make([
                'id' => $this->absenceRequestForm->id,
                'rejection_reason' => $this->rejectionReason,
            ])
                ->checkPermission()
                ->validate()
                ->execute();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->notification()->success(__('Vacation request rejected'));
        $this->loadData();

        return true;
    }
}
