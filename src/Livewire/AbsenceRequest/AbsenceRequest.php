<?php

namespace FluxErp\Livewire\AbsenceRequest;

use FluxErp\Enums\AbsenceRequestStateEnum;
use FluxErp\Htmlables\TabButton;
use FluxErp\Livewire\Forms\AbsenceRequestForm;
use FluxErp\Models\AbsenceRequest as AbsenceRequestModel;
use FluxErp\Traits\Livewire\Actions;
use FluxErp\Traits\Livewire\WithTabs;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Renderless;
use Livewire\Component;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Exceptions\UnauthorizedException;

class AbsenceRequest extends Component
{
    use Actions, WithTabs;

    public AbsenceRequestForm $absenceRequestForm;

    #[Locked]
    public array $activities = [];

    public array $queryString = [
        'tab' => ['except' => 'absence-request.general'],
    ];

    public string $tab = 'absence-request.general';

    public function mount(AbsenceRequestModel $id): void
    {
        $this->absenceRequestForm->fill($id);

        $this->getStatusChanges();
    }

    public function render(): View
    {
        return view('flux::livewire.human-resources.absence-request');
    }

    #[Renderless]
    public function approve(): bool
    {
        try {
            $this->absenceRequestForm->changeState(AbsenceRequestStateEnum::Approved);
        } catch (UnauthorizedException|ValidationException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->absenceRequestForm->reset('comment');
        $this->toast()
            ->success(__('Absence request has been approved'))
            ->send();
        $this->getStatusChanges();

        return true;
    }

    public function getEmployeeUrl(): string
    {
        return route(
            'human-resources.employees.id',
            ['id' => $this->absenceRequestForm->employee_id]
        );
    }

    #[Renderless]
    public function getStatusChanges(): void
    {
        $model = resolve_static(AbsenceRequestModel::class, 'query')
            ->whereKey($this->absenceRequestForm->id)
            ->first();

        $this->activities = $model
            ->activities()
            ->where('log_name', 'absence_request_state_changes')
            ->with('causer')
            ->latest()
            ->get([
                'id',
                'causer_type',
                'causer_id',
                'event',
                'description',
                'created_at',
            ])
            ->map(function (Activity $activity) {
                $activityArray = $activity->toArray();
                $activityArray['causer'] = [
                    'name' => $activity->causer?->name ?? __('System'),
                    'avatar_url' => $activity->causer?->getAvatarUrl(),
                ];

                return $activityArray;
            })
            ->toArray();
    }

    public function getTabs(): array
    {
        return [
            TabButton::make('absence-request.general')
                ->text(__('General')),
            TabButton::make('absence-request.approval')
                ->text(__('Approval')),
            TabButton::make('absence-request.employee-days')
                ->text(__('Employee Days'))
                ->isLivewireComponent()
                ->wireModel('absenceRequestForm.id'),
            TabButton::make('absence-request.comments')
                ->text(__('Comments'))
                ->isLivewireComponent()
                ->wireModel('absenceRequestForm.id'),
            TabButton::make('absence-request.activities')
                ->text(__('Activities'))
                ->isLivewireComponent()
                ->wireModel('absenceRequestForm.id'),
        ];
    }

    #[Renderless]
    public function reject(): bool
    {
        try {
            $this->absenceRequestForm->changeState(AbsenceRequestStateEnum::Rejected);
        } catch (UnauthorizedException|ValidationException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->absenceRequestForm->reset('comment');
        $this->toast()
            ->success(__('Absence request has been rejected'))
            ->send();
        $this->getStatusChanges();

        return true;
    }

    public function resetForm(): void
    {
        $absenceRequest = resolve_static(AbsenceRequestModel::class, 'query')
            ->whereKey($this->absenceRequestForm->id)
            ->with(['user', 'absenceType', 'substitute', 'approvedBy', 'media'])
            ->firstOrFail();

        $this->absenceRequestForm->reset();
        $this->absenceRequestForm->fill($absenceRequest);
    }

    #[Renderless]
    public function revoke(): bool
    {
        try {
            $this->absenceRequestForm->changeState(AbsenceRequestStateEnum::Revoked);
        } catch (UnauthorizedException|ValidationException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->absenceRequestForm->reset('comment');
        $this->toast()
            ->success(__('Absence request has been canceled'))
            ->send();
        $this->getStatusChanges();

        return true;
    }

    #[Renderless]
    public function save(): bool
    {
        try {
            $this->absenceRequestForm->save();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->absenceRequestForm->reset('comment');
        $this->toast()
            ->success(__(':model saved', ['model' => __('Absence Request')]))
            ->send();

        return true;
    }
}
