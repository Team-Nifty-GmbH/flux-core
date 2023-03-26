<?php

namespace FluxErp\Http\Livewire\Settings;

use FluxErp\Helpers\Helper;
use FluxErp\Http\Requests\CreateAdditionalColumnRequest;
use FluxErp\Http\Requests\UpdateAdditionalColumnRequest;
use FluxErp\Models\AdditionalColumn;
use FluxErp\Rules\AvailableValidationRule;
use FluxErp\Rules\UniqueInFieldDependence;
use FluxErp\Services\AdditionalColumnService;
use FluxErp\Traits\HasAdditionalColumns;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use WireUi\Traits\Actions;

class AdditionalColumnEdit extends Component
{
    use Actions;

    public array $additionalColumn;

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

    protected function getRules(): array
    {
        $rules = $this->isNew ?
            (new CreateAdditionalColumnRequest())->rules() : (new UpdateAdditionalColumnRequest())->rules();

        foreach ($rules['name'] as $key => $rule) {
            if ($rule instanceof UniqueInFieldDependence) {
                $rules['name'][$key] = $this->isNew ?
                    new UniqueInFieldDependence(
                        AdditionalColumn::class,
                        ['additionalColumn.model_type', 'additionalColumn.model_id'],
                        ! $this->isNew
                    ) :
                    new UniqueInFieldDependence(
                        AdditionalColumn::class,
                        ['additionalColumn.model_type', 'additionalColumn.model_id'],
                        ! $this->isNew,
                        'additionalColumn.id'
                    );
            }
        }

        return Arr::prependKeysWith($rules, 'additionalColumn.');
    }

    public function boot(): void
    {
        $this->additionalColumn = array_fill_keys(
            array_keys((new CreateAdditionalColumnRequest())->rules()),
            null
        );

        $this->additionalColumn['validations'] = [];
        $this->additionalColumn['values'] = [];
        $this->additionalColumn['is_customer_editable'] = false;

        $appModels = get_subclasses_of(Model::class, 'FluxErp\\');
        $moduleModels = get_subclasses_of(Model::class, 'Modules\\');

        $models = array_merge($appModels, $moduleModels);

        foreach ($models as $model) {
            if (in_array(HasAdditionalColumns::class, class_uses_recursive($model))) {
                $this->models[] = $model;
            }
        }

        $this->fieldTypes = Helper::getHtmlInputFieldTypes();

        $availableValidationRules = (new AvailableValidationRule())->availableValidationRules;

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
        $this->additionalColumn = $additionalColumn ?:
            array_fill_keys(
                array_keys((new CreateAdditionalColumnRequest())->rules()),
                null
            );

        if (is_null($this->additionalColumn['validations'] ?? null)) {
            $this->additionalColumn['validations'] = [];
        }

        if (is_null($this->additionalColumn['values'] ?? null)) {
            $this->additionalColumn['values'] = [];
        }

        if (is_null($this->additionalColumn['is_customer_editable'] ?? null)) {
            $this->additionalColumn['is_customer_editable'] = false;
        }

        if (is_null($this->additionalColumn['is_translatable'] ?? null)) {
            $this->additionalColumn['is_translatable'] = false;
        }

        if (is_null($this->additionalColumn['is_frontend_visible'] ?? null)) {
            $this->additionalColumn['is_frontend_visible'] = true;
        }

        $this->isNew = ! array_key_exists('id', $this->additionalColumn);
        $this->hideModel = $this->additionalColumn['model_type'] && $this->additionalColumn['model_id'];
    }

    public function addEntry(): void
    {
        $this->additionalColumn['values'][] = null;
    }

    public function removeEntry(int $index): void
    {
        unset($this->additionalColumn['values'][$index]);
        $this->additionalColumn['values'] = array_values($this->additionalColumn['values'] ?? []);
    }

    /**
     * @throws ValidationException
     */
    public function save(): void
    {
        if (($this->isNew && ! user_can('api.additional-columns.{id}.post')) ||
            (! $this->isNew && ! user_can('api.additional-columns.{id}.put'))
        ) {
            $this->notification()->error(
                __('insufficient permissions'),
                __('You have not the rights to modify this record')
            );

            return;
        }

        $this->additionalColumn['model_id'] = $this->additionalColumn['model_id'] ?? null;
        $this->additionalColumn['values'] = array_filter($this->additionalColumn['values']);

        if ($this->additionalColumn['values']) {
            $this->additionalColumn['values'] = array_values(
                array_unique($this->additionalColumn['values'])
            );
        }

        $validated = $this->validate();

        $additionalColumnService = new AdditionalColumnService();
        $response = $additionalColumnService->{$this->isNew ? 'create' : 'update'}($validated['additionalColumn']);

        if ($response['status'] > 299) {
            $this->notification()->error(
                implode(',', array_keys($response['errors'])),
                implode(', ', Arr::dot($response['errors']))
            );

            return;
        }

        $this->notification()->success(__('Additional Column saved successful.'));

        $this->skipRender();
        $this->emitUp('closeModal', $response['data']);
    }

    public function delete(): void
    {
        if (! user_can('api.additional-columns.{id}.delete')) {
            return;
        }

        (new AdditionalColumnService())->delete($this->additionalColumn['id']);

        $this->skipRender();
        $this->emitUp('closeModal', $this->additionalColumn, true);
    }
}
