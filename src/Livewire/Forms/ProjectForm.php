<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\Project\CreateProject;
use FluxErp\Actions\Project\UpdateProject;
use FluxErp\Models\Client;
use Illuminate\Support\Arr;
use Livewire\Attributes\Locked;
use Livewire\Form;

class ProjectForm extends Form
{
    public array $additionalColumns = [];

    public ?string $budget = null;

    public ?int $client_id = null;

    public ?int $contact_id = null;

    public ?string $description = null;

    public ?string $end_date = null;

    #[Locked]
    public ?int $id = null;

    public ?string $name = null;

    public ?int $order_id = null;

    public ?int $parent_id = null;

    public ?string $project_number = null;

    public ?int $responsible_user_id = null;

    public ?string $start_date = null;

    public string $state = 'open';

    public ?string $time_budget = null;

    public function reset(...$properties): void
    {
        parent::reset(...$properties);

        $this->client_id = resolve_static(Client::class, 'default')?->getKey();
    }

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
