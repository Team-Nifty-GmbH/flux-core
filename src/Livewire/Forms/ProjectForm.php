<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\Project\CreateProject;
use FluxErp\Actions\Project\UpdateProject;
use Illuminate\Support\Arr;
use Livewire\Form;

class ProjectForm extends Form
{
    public ?int $id = null;

    public ?int $contact_id = null;

    public ?int $order_id = null;

    public ?int $responsible_user_id = null;

    public ?int $parent_id = null;

    public ?string $name = null;

    public ?string $start_date = null;

    public ?string $end_date = null;

    public ?string $description = null;

    public string $state = 'open';

    public ?string $time_budget = null;

    public ?string $budget = null;

    public array $additionalColumns = [];

    public function save(): void
    {
        if (! is_null($this->time_budget) && preg_match('/[0-9]*/', $this->time_budget)) {
            $this->time_budget = $this->time_budget . ':00';
        }

        $data = $this->toArray();
        $data = array_merge(Arr::pull($data, 'additionalColumns', []), $data);

        $action = $this->id ? UpdateProject::make($data) : CreateProject::make($data);

        $response = $action->validate()->execute();

        $this->fill($response);
    }
}
