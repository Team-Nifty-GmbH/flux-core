<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\Project\CreateProject;
use FluxErp\Actions\Project\UpdateProject;
use Illuminate\Support\Carbon;
use Livewire\Form;

class ProjectForm extends Form
{
    public ?int $id = null;

    public ?int $contact_id = null;

    public ?int $order_id = null;

    public ?int $parent_id = null;

    public ?string $name = null;

    public ?Carbon $start_date = null;

    public ?Carbon $end_date = null;

    public ?string $description = null;

    public string $state = 'open';

    public ?string $time_budget_hours = null;

    public ?string $budget = null;

    public function save(): void
    {
        $action = $this->id ? UpdateProject::make($this->toArray()) : CreateProject::make($this->toArray());

        $response = $action->validate()->execute();

        $this->fill($response);
    }
}
