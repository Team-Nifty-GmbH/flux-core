<?php

namespace FluxErp\Livewire\Employee;

use Exception;
use FluxErp\Htmlables\TabButton;
use FluxErp\Livewire\Forms\EmployeeForm;
use FluxErp\Models\Employee as EmployeeModel;
use FluxErp\Traits\Livewire\Actions;
use FluxErp\Traits\Livewire\WithFileUploads;
use FluxErp\Traits\Livewire\WithTabs;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Renderless;
use Livewire\Attributes\Url;
use Livewire\Component;
use Spatie\Permission\Exceptions\UnauthorizedException;

class Employee extends Component
{
    use Actions, WithFileUploads, WithTabs;

    public ?string $avatar = null;

    public EmployeeForm $employee;

    #[Url]
    public string $tab = 'employee.dashboard';

    public function mount(int $id): void
    {
        $employee = resolve_static(EmployeeModel::class, 'query')
            ->whereKey($id)
            ->with([
                'media' => fn (MorphMany $query) => $query->where('collection_name', 'avatar'),
                'workTimeModelHistory' => function ($query): void {
                    $query->whereNull('valid_until')
                        ->with('workTimeModel');
                },
                'employeeDepartment:id',
                'location:id',
                'supervisor:id',
            ])
            ->firstOrFail();

        $this->employee->fill($employee);
        $this->avatar = $employee->getAvatarUrl();
    }

    public function render(): View|Factory|Application
    {
        return view('flux::livewire.human-resources.employee');
    }

    public function getTabs(): array
    {
        return [
            TabButton::make('employee.dashboard')
                ->text(__('Dashboard'))
                ->isLivewireComponent()
                ->wireModel('employee.id'),
            TabButton::make('employee.general')
                ->text(__('General')),
            TabButton::make('employee.absence-requests')
                ->text(__('Absence Requests'))
                ->isLivewireComponent()
                ->wireModel('employee.id'),
            TabButton::make('employee.employee-balance-adjustments')
                ->text(__('Employee Balance Adjustments'))
                ->isLivewireComponent()
                ->wireModel('employee.id'),
            TabButton::make('employee.employee-days')
                ->text(__('Employee Days'))
                ->isLivewireComponent()
                ->wireModel('employee.id'),
            TabButton::make('employee.attachments')
                ->text(__('Attachments'))
                ->isLivewireComponent()
                ->wireModel('employee.id'),
            TabButton::make('employee.comments')
                ->text(__('Comments'))
                ->isLivewireComponent()
                ->wireModel('employee.id'),
        ];
    }

    public function resetForm(): void
    {
        $employee = resolve_static(EmployeeModel::class, 'query')
            ->whereKey($this->employee->id)
            ->firstOrFail();

        $this->employee->reset();
        $this->employee->fill($employee);
    }

    #[Renderless]
    public function save(): bool
    {
        try {
            $this->employee->save();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->toast()
            ->success(__(':model saved', ['model' => __('Employee')]))
            ->send();

        return true;
    }

    public function updatedAvatar(): void
    {
        $this->collection = 'avatar';
        try {
            $this->saveFileUploadsToMediaLibrary(
                'avatar',
                $this->employee->id,
                morph_alias(EmployeeModel::class)
            );
        } catch (Exception $e) {
            exception_to_notifications($e, $this);

            return;
        }

        $this->avatar = resolve_static(EmployeeModel::class, 'query')
            ->whereKey($this->employee->id)
            ->first()
            ->getAvatarUrl();
    }
}
