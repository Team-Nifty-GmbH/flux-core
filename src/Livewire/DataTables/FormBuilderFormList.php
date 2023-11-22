<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Actions\FormBuilderField\DeleteFormBuilderField;
use FluxErp\Actions\FormBuilderForm\DeleteFormBuilderForm;
use FluxErp\Actions\FormBuilderSection\DeleteFormBuilderSection;
use FluxErp\Enums\FormBuilderTypeEnum;
use FluxErp\Livewire\Forms\FormBuilderForm as FormBuilderFormForm;
use FluxErp\Models\FormBuilderForm;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Exceptions\UnauthorizedException;
use TeamNiftyGmbH\DataTable\DataTable;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;

class FormBuilderFormList extends DataTable
{
    protected string $model = FormBuilderForm::class;

    protected string $view = 'flux::livewire.settings.form-builder';

    public array $enabledCols = [
        'name',
        'description',
        'slug',
        'is_active',
        'start_date',
        'end_date',
    ];

    public array $options = [

    ];

    public bool $showModal = false;

    public FormBuilderFormForm $form;

    public array $fieldTypes = [];

    public function mount(): void
    {
        parent::mount();
        $this->fieldTypes = FormBuilderTypeEnum::cases();
    }

    public function getRowActions(): array
    {
        return [
            DataTableButton::make()
                ->label(__('Edit'))
                ->icon('pencil')
                ->color('primary')
                ->attributes([
                    'x-on:click' => '$wire.editItem(record.id)',
                ]),
            DataTableButton::make()
                ->label(__('Preview'))
                ->icon('eye')
                ->color('positive')
                ->attributes([
                    'x-on:click' => '$wire.preview(record.id)',
                ]),
            DataTableButton::make()
                ->label(__('Delete'))
                ->icon('trash')
                ->color('negative')
                ->attributes([
                    'x-on:click' => '$wire.deleteItem(record.id)',
                    'wire:loading.attr' => 'disabled',
                ]),

        ];
    }

    public function getTableActions(): array
    {
        return [
            DataTableButton::make()
                ->label(__('Create'))
                ->icon('plus')
                ->color('primary')
                ->attributes([
                    'x-on:click' => '$wire.newItem()',
                ]),
        ];
    }

    public function boot(): void
    {
        // override boot to force rendering
    }

    public function newItem(): void
    {
        $this->form->fill(new FormBuilderForm());
        $this->form->sections = [];
        $this->showModal = true;
    }

    public function deleteItem(FormBuilderForm $form): void
    {
        $this->skipRender();

        try {
            DeleteFormBuilderForm::make($form->toArray())
                ->checkPermission()
                ->validate()
                ->execute();
        } catch (\Exception $e) {
            exception_to_notifications($e, $this);

            return;
        }

        $this->loadData();
    }

    public function editItem(FormBuilderForm $formBuilderForm): void
    {
        $this->form->fill($formBuilderForm);

        $this->showModal = true;
    }

    public function saveItem(): void
    {
        try {
            $this->form->save();

            $this->form->reset();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return;
        }

        $this->showModal = false;
    }

    public function addSection(): void
    {
        $this->form->sections ??= [];
        $this->form->sections[] = [
            'id' => null,
            'name' => null,
            'description' => null,
            'columns' => null,
            'fields' => [],
        ];
    }

    public function removeFormSection(int $sectionIndex): void
    {
        $section = $this->form->sections[$sectionIndex];
        if (! is_null($section['id'])) {
            DeleteFormBuilderSection::make($section)->execute();
        }
        unset($this->form->sections[$sectionIndex]);
    }

    public function addFormField(int $index): void
    {

        $this->form->sections[$index]['fields'][] = [
            'id' => null,
            'name' => null,
            'description' => null,
            'type' => 'text',
            'options' => null,
        ];
    }

    public function closeModal(): void
    {
        $this->showModal = false;
    }

    public function removeFormField(int $sectionIndex, int $fieldIndex): void
    {
        $field = $this->form->sections[$sectionIndex]['fields'][$fieldIndex];
        if (! is_null($field['id'])) {
            DeleteFormBuilderField::make($field)->execute();
        }
        unset($this->form->sections[$sectionIndex]['fields'][$fieldIndex]);
    }

    public function debug(): void
    {
        dd($this->form->toArray());
    }
}
