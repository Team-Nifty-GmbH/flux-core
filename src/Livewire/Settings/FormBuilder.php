<?php

namespace FluxErp\Livewire\Settings;

use FluxErp\Models\FormBuilderForm;
use Livewire\Component;

class FormBuilder extends Component
{
    public bool $showModal = false;

    public array $form = [];

    public array $formData = [];

    public array $fieldTypes = [
        'text',
        'textarea',
        'select',
        'radio',
        'checkbox',
        'date',
        'time',
        'datetime',
        'file',
        'image',
        'number',
        'email',
        'password',
        'range',
        ];

    public function mount()
    {
        $this->resetForm();
    }

    public function render()
    {
        return view('flux::livewire.settings.form-builder');
    }

    public function editItem($id = null)
    {
        $id == null ? $this->resetForm() : $this->form = FormBuilderForm::find($id)->toArray();
        $this->showModal = true;
    }

    public function saveItem()
    {
        //        $this->validate([
        //            'form.name' => 'string|required',
        //            'form.description' => 'string|required',
        //            'form.slug' => 'string|required',
        //            'form.details' => 'boolean',
        //            'form.start_date' => 'date',
        //            'form.end_date' => 'date',
        //        ]);

        //        dd($this->form);

        $this->showModal = false;

        if (isset($this->form['id'])) {
            FormBuilderForm::find($this->form['id'])->update($this->form);
        } else {
            FormBuilderForm::create($this->form);
        }

        $this->resetForm();
    }

    public function addSection()
    {
        $this->formData['sections'][] = [
            'name' => null,
            'ordering' => null,
            'columns' => null,
            'description' => null,
            'icon' => null,
            'aside' => false,
            'compact' => false,
        ];
    }

    public function editSection()
    {

    }

    public function deleteSection()
    {

    }

    public function addFormField($index)
    {
        $this->formData['sections'][$index]['fields'][] = [
            'name' => null,
            'description' => null,
            'type' => null,
            'ordering' => null,
            'options' => true,
        ];
    }

    public function editFormFiled()
    {

    }

    public function deleteFormField()
    {

    }

    public function resetForm(): void
    {
        $this->form = [
            'name' => null,
            'description' => null,
            'slug' => null,
            'details' => null,
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
}
