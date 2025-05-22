<?php

namespace FluxErp\Livewire\Lead;

use FluxErp\Actions\Tag\CreateTag;
use FluxErp\Livewire\Forms\LeadForm;
use FluxErp\Models\Lead as LeadModel;
use FluxErp\Models\LeadState;
use FluxErp\Traits\Livewire\Actions;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Modelable;
use Livewire\Attributes\Renderless;
use Livewire\Component;
use Spatie\Permission\Exceptions\UnauthorizedException;

class General extends Component
{
    use Actions;

    public bool $isLoss = false;

    #[Modelable]
    public LeadForm $leadForm;

    public function mount(): void
    {
        $this->isLoss = resolve_static(LeadState::class, 'query')
            ->whereKey($this->leadForm->lead_state_id)
            ->value('is_loss') ?? false;
    }

    public function render(): View
    {
        return view('flux::livewire.lead.general');
    }

    #[Renderless]
    public function addTag(string $name): void
    {
        try {
            $tag = CreateTag::make([
                'name' => $name,
                'type' => morph_alias(LeadModel::class),
            ])
                ->checkPermission()
                ->validate()
                ->execute();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return;
        }

        $this->leadForm->tags[] = $tag->id;
        $this->js(<<<'JS'
            edit = true;
        JS);
    }

    #[Renderless]
    public function evaluate(): void {}
}
