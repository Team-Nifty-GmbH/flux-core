<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\FormBuilderForm;
use FluxErp\Models\FormBuilderSection;
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
        'details',
        'is_active',
        'start_date',
        'end_date',
    ];

    public bool $showModal = false;

    public array $form = [];

    public array $formData = [];

    public array $fieldTypes = [
        ['value' => 'text', 'name' => 'Text'],
        ['value' => 'textarea', 'name' => 'Textarea'],
        ['value' => 'select', 'name' => 'Select'],
        ['value' => 'radio', 'name' => 'Radio'],
        ['value' => 'checkbox', 'name' => 'Checkbox'],
        ['value' => 'date', 'name' => 'Date'],
        ['value' => 'time', 'name' => 'Time'],
        ['value' => 'datetime', 'name' => 'Datetime'],
        ['value' => 'file', 'name' => 'File'],
        ['value' => 'image', 'name' => 'Image'],
        ['value' => 'number', 'name' => 'Number'],
        ['value' => 'email', 'name' => 'Email'],
        ['value' => 'password', 'name' => 'Password'],
        ['value' => 'range', 'name' => 'Range'],
    ];


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


    public function editItem($id = null)
    {
        $id == null ? $this->resetForm() : $this->form = FormBuilderForm::find($id)->toArray();
        $this->showModal = true;
    }

    public function saveItem()
    {
        $this->validate([
            'form.name' => 'string|required',
            'form.description' => 'string|required',
            'form.slug' => 'string|required',
            'form.is_active' => 'boolean',
            'form.start_date' => 'date|nullable',
            'form.end_date' => 'date|nullable',
            'formData' => 'array',
            'formData.*.name' => 'string|required',
            'formData.*.ordering' => 'integer|required',
            'formData.*.columns' => 'integer|required',
            'formData.*.description' => 'string|required',
            'formData.*.icon' => 'string|required',
            'formData.*.aside' => 'boolean',
            'formData.*.compact' => 'boolean',
            'formData.*.fields' => 'array',
            'formData.*.fields.*.name' => 'string|required',
            'formData.*.fields.*.description' => 'string|required',
            'formData.*.fields.*.type' => 'string|required',
            'formData.*.fields.*.ordering' => 'integer|required',
            'formData.*.fields.*.options' => 'boolean',
        ]);

//        dd($this->form);

        $this->showModal = false;

        if (isset($this->form['id'])) {
            $formBuilderForm = FormBuilderForm::find($this->form['id'])->update($this->form);
        } else {
            $formBuilderForm = FormBuilderForm::create($this->form);
        }
dd($formBuilderForm);
        foreach ($this->formData as $section) {
            if ($section['id'] !== null) {
                $formBuilderSection = FormBuilderSection::find($section['id'])->update($section);
            } else {
                $formBuilderSection = FormBuilderSection::create($section);
            }

            foreach ($section['fields'] as $field) {
                if ($field['id'] !== null) {
                    $formBuilderSection->fields()->find($field['id'])->update($field);
                } else {
                    $formBuilderSection->fields()->create($field);
                }
            }
        }

        $this->resetForm();
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

    public function resetForm(): void
    {
        $this->form = [
            'name' => null,
            'description' => null,
            'slug' => null,
            'is_active' => true,
            'start_date' => null,
            'end_date' => null,
        ];
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->formData = [];
        $this->resetForm();
    }

    public function debug()
    {
        dd($this->form, $this->formData);
    }
}
