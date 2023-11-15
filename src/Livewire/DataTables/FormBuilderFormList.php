<?php

namespace FluxErp\Livewire\DataTables;

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

    public bool $showModal = false;

    public FormBuilderFormForm $form;

    public array $formData = [];

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
                    'x-on:click' => '$wire.editItem(null)',
                ]),
        ];
    }

    public function deleteItem(FormBuilderForm $form): void
    {
        //        $this->skipRender();
        //
        //        try {
        //            DeleteFormBuilderForm::make($form->toArray())
        //                ->checkPermission()
        //                ->validate()
        //                ->execute();
        //        } catch (\Exception $e) {
        //            exception_to_notifications($e, $this);
        //
        //            return;
        //        }
        //
        //        $this->loadData();
    }

    public function editItem(FormBuilderForm $formBuilderForm)
    {
        $this->form->fill($formBuilderForm);

        $this->showModal = true;
    }

    public function saveItem()
    {
        try {
            $this->form->save();

            $this->form->reset();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);
            dd($e);

            return;
        }

        $this->showModal = false;
    }

    public function boot(): void
    {
        // override boot to force rendering
    }

    public function addSection()
    {
        $this->formData[] = [
            'id' => null,
            'name' => null,
            'ordering' => null,
            'columns' => null,
            'description' => null,
            'icon' => null,
            'aside' => false,
            'compact' => false,
        ];
    }

    public function deleteSection()
    {

    }

    public function addFormField(int $index)
    {

        $this->formData[$index]['fields'][] = [
            'id' => null,
            'name' => null,
            'description' => null,
            'type' => 'text',
            'ordering' => null,
            'options' => true,
        ];
    }

    public function saveFormField()
    {

    }

    public function deleteFormField(int $index)
    {
        unset($this->formData[$index]);
    }

    public function debug()
    {
        dd($this->form, $this->formData);
    }
}
