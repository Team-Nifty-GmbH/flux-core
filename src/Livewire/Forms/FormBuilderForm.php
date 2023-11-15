<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\FormBuilderField\CreateFormBuilderField;
use FluxErp\Actions\FormBuilderField\UpdateFormBuilderField;
use FluxErp\Actions\FormBuilderForm\CreateFormBuilderForm;
use FluxErp\Actions\FormBuilderForm\UpdateFormBuilderForm;
use FluxErp\Actions\FormBuilderSection\CreateFormBuilderSection;
use FluxErp\Actions\FormBuilderSection\UpdateFormBuilderSection;
use Livewire\Form;

class FormBuilderForm extends Form
{
    public ?int $id = null;

    public ?string $name = null;

    public ?string $description = null;

    public ?string $slug = null;

    public ?bool $is_active = true;

    public ?string $start_date = null;

    public ?string $end_date = null;

    public array $sections = [];

    public function save(): void
    {
        $action = $this->id ? UpdateFormBuilderForm::make($this->toArray()) : CreateFormBuilderForm::make($this->toArray());

        $response = $action->checkPermission()->validate()->execute();

        foreach ($this->sections as $section) {
            $section['form_id'] = $response->id;
            $actionSection = $section['id'] ? UpdateFormBuilderSection::make($section) : CreateFormBuilderSection::make($section);
            $responseSection = $actionSection->checkPermission()->validate()->execute();

            foreach ($section['fields'] as $field) {
                $field['section_id'] = $responseSection->id;
                $actionField = $field['id'] ? UpdateFormBuilderField::make($field) : CreateFormBuilderField::make($field);
                $responseField = $action->checkPermission()->validate()->execute();
            }
        }

        $this->fill($response);
    }

    public function fill($values)
    {
        parent::fill($values);

        $this->sections = $values->sections->map(fn ($section) => $section->load('fields'))->toArray();
    }
}
