<?php

namespace FluxErp\Livewire\Order;

use FluxErp\Livewire\Forms\ProjectForm;
use FluxErp\Models\Order;
use FluxErp\Models\Project;
use FluxErp\Rules\ModelExists;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Livewire\Attributes\Lazy;
use Livewire\Component;
use Spatie\Permission\Exceptions\UnauthorizedException;
use WireUi\Traits\Actions;

#[Lazy]
class OrderProject extends Component
{
    use Actions;

    public ProjectForm $form;

    public ?int $projectId = null;

    public bool $existingProject = true;

    public function mount(Order $order): void
    {
        $this->form->order_id = $order->id;
        $this->form->client_id = $order->client_id;
        $this->form->contact_id = $order->contact_id;
        $this->form->start_date = $order->system_delivery_date ?? now();
        $this->form->end_date = $order->system_delivery_date_end;
        $this->form->responsible_user_id = auth()->id();
        $this->form->name = $order->getLabel();
    }

    public function render(): View
    {
        return view('flux::livewire.order.order-project');
    }

    public function placeholder(): View
    {
        return view('flux::livewire.placeholders.box');
    }

    public function save(): bool
    {
        if ($this->existingProject && ! $this->projectId) {
            try {
                $this->validate([
                    'projectId' => [
                        'required',
                        'integer',
                        app(ModelExists::class, ['model' => Project::class]),
                    ],
                ]);
            } catch (ValidationException $e) {
                exception_to_notifications($e, $this);

                return false;
            }
        }

        if ($this->existingProject) {
            $project = resolve_static(Project::class, 'query')
                ->whereKey($this->projectId)
                ->first();
            $project->order_id ??= $this->form->order_id;
            $project->client_id ??= $this->form->client_id;
            $project->contact_id ??= $this->form->contact_id;
            $project->start_date ??= $this->form->start_date;
            $project->end_date ??= $this->form->end_date;
            $project->responsible_user_id ??= $this->form->responsible_user_id;
            $project->name ??= $this->form->name;

            $this->form->fill($project);
        }

        try {
            $this->form->save();
        } catch (UnauthorizedException|ValidationException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $id = $this->projectId;
        $this->js(<<<JS
            \$wire.\$parent.createTasks($id);
        JS);

        return true;
    }
}
