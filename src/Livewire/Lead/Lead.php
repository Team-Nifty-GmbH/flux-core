<?php

namespace FluxErp\Livewire\Lead;

use FluxErp\Htmlables\TabButton;
use FluxErp\Livewire\Forms\LeadForm;
use FluxErp\Models\Lead as LeadModel;
use FluxErp\Traits\Livewire\Actions;
use FluxErp\Traits\Livewire\WithTabs;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Renderless;
use Livewire\Component;
use Spatie\Permission\Exceptions\UnauthorizedException;

class Lead extends Component
{
    use Actions, WithTabs;

    public ?string $avatar = null;

    public LeadForm $leadForm;

    public array $queryString = [
        'tab' => ['except' => 'lead.general'],
    ];

    public string $tab = 'lead.general';

    public function mount(LeadModel $id): void
    {
        $this->leadForm->fill($id);
    }

    public function render(): View
    {
        return view('flux::livewire.lead.lead');
    }

    public function getTabs(): array
    {
        return [
            TabButton::make('lead.general')
                ->isLivewireComponent()
                ->wireModel('leadForm')
                ->text(__('General')),
            TabButton::make('lead.tasks')
                ->isLivewireComponent()
                ->wireModel('leadForm.id')
                ->text(__('Tasks')),
            TabButton::make('lead.comments')
                ->isLivewireComponent()
                ->wireModel('leadForm.id')
                ->text(__('Comments')),
            TabButton::make('lead.calendar')
                ->isLivewireComponent()
                ->wireModel('leadForm.id')
                ->text(__('Calendar')),
            TabButton::make('lead.attachments')
                ->isLivewireComponent()
                ->wireModel('leadForm.id')
                ->text(__('Attachments')),
        ];
    }

    #[Renderless]
    public function resetForm(): void
    {
        $lead = resolve_static(LeadModel::class, 'query')
            ->whereKey($this->leadForm->id)
            ->with(['tags:id', 'categories:id'])
            ->firstOrFail();

        $this->leadForm->reset();
        $this->leadForm->fill($lead);
    }

    #[Renderless]
    public function save(): array|bool
    {
        try {
            $this->leadForm->save();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->toast()
            ->success(__(':model saved', ['model' => __('Lead')]))
            ->send();

        return true;
    }
}
