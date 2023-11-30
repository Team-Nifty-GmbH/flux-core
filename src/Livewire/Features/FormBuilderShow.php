<?php

namespace FluxErp\Livewire\Features;

use FluxErp\Models\FormBuilderForm;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class FormBuilderShow extends Component
{
    public array $fieldTypes = [];

    public int $formId;

    public array $form = [];

    public array $fieldResponses = [];

    public function mount(): void
    {
        $this->form = FormBuilderForm::query()->whereKey($this->formId)->with(['sections', 'sections.fields'])->first()->toArray();
    }

    public function render(): View
    {
        return view('flux::livewire.features.form-builder-show');
    }

    public function submitForm(): void
    {
        dd($this->form);
    }
}
