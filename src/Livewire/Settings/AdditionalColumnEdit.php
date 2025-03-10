<?php

namespace FluxErp\Livewire\Settings;

use FluxErp\Helpers\Helper;
use FluxErp\Livewire\Forms\AdditionalColumnForm;
use FluxErp\Rules\AvailableValidationRule;
use FluxErp\Traits\HasAdditionalColumns;
use FluxErp\Traits\Livewire\Actions;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Renderless;
use Livewire\Component;
use Spatie\Permission\Exceptions\UnauthorizedException;

class AdditionalColumnEdit extends Component
{
    use Actions;

    public AdditionalColumnForm $additionalColumn;

    public array $models;

    public array $fieldTypes;

    public bool $isNew = true;

    public bool $hideModel = false;

    public array $availableValidationRules;

    protected $listeners = [
        'show',
        'save',
        'delete',
    ];

    public function mount(): void
    {

        $this->models = model_info_all()
            ->unique('morphClass')
            ->filter(fn ($modelInfo) => in_array(
                HasAdditionalColumns::class,
                class_uses_recursive($modelInfo->class)
            ))
            ->map(fn ($modelInfo) => [
                'label' => __(Str::headline($modelInfo->morphClass)),
                'value' => $modelInfo->morphClass,
            ])
            ->sortBy('label')
            ->toArray();

        $this->fieldTypes = Helper::getHtmlInputFieldTypes();

        $availableValidationRules = app(AvailableValidationRule::class)->availableValidationRules;

        $this->availableValidationRules = array_filter($availableValidationRules, function ($item) {
            return ! str_contains($item, ':');
        });
    }

    public function render(): View
    {
        return view('flux::livewire.settings.additional-column-edit');
    }

    public function show(array $additionalColumn = []): void
    {
        $additionalColumn['values'] ??= [];
        $additionalColumn['validations'] ??= [];
        $additionalColumn['field_type'] ??= 'text';
        $additionalColumn['is_translatable'] ??= false;
        $additionalColumn['is_customer_editable'] ??= false;
        $additionalColumn['is_frontend_visible'] ??= true;

        $this->additionalColumn->reset();
        $this->additionalColumn->fill($additionalColumn);

        $this->isNew = ! $this->additionalColumn->id;
        $this->hideModel = $this->additionalColumn->model_type && $this->additionalColumn->model_id;
    }

    public function addEntry(): void
    {
        $this->additionalColumn->values[] = null;
    }

    public function removeEntry(int $index): void
    {
        unset($this->additionalColumn->values[$index]);
        $this->additionalColumn->values = array_values($this->additionalColumn->values ?? []);
    }

    #[Renderless]
    public function save(): void
    {
        try {
            $this->additionalColumn->save();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return;
        }

        $this->notification()->success(__(':model saved', ['model' => __('Additional Column')]))->send();
        $this->dispatch('closeModal', $this->additionalColumn->toArray());
    }

    #[Renderless]
    public function delete(): void
    {
        try {
            $this->additionalColumn->delete();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return;
        }

        $this->dispatch('closeModal', $this->additionalColumn, true);
    }
}
