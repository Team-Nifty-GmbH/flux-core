<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\FluxAction;
use FluxErp\Actions\WorkTime\CreateWorkTime;
use FluxErp\Actions\WorkTime\DeleteWorkTime;
use FluxErp\Actions\WorkTime\UpdateWorkTime;
use Livewire\Attributes\Locked;

class WorkTimeForm extends FluxForm
{
    public ?int $contact_id = null;

    public ?string $description = null;

    #[Locked]
    public ?string $ended_at = null;

    #[Locked]
    public ?int $id = null;

    public ?bool $is_billable = null;

    #[Locked]
    public bool $is_daily_work_time = false;

    #[Locked]
    public bool $is_locked = false;

    #[Locked]
    public bool $is_pause = false;

    public ?string $name = null;

    #[Locked]
    public ?int $order_position_id = null;

    #[Locked]
    public ?int $parent_id = null;

    #[Locked]
    public ?int $paused_time_ms = null;

    #[Locked]
    public ?string $started_at = null;

    public ?int $trackable_id = null;

    public ?string $trackable_type = null;

    public ?int $user_id = null;

    public ?int $work_time_type_id = null;

    public function __toString(): string
    {
        return (string) $this->id;
    }

    protected function getActions(): array
    {
        return [
            'create' => CreateWorkTime::class,
            'update' => UpdateWorkTime::class,
            'delete' => DeleteWorkTime::class,
        ];
    }

    protected function makeAction(string $name, ?array $data = null): FluxAction
    {
        $this->user_id = $this->user_id ?? auth()->id();

        $workTime = $data ?? $this->toArray();
        if (! $workTime['trackable_type'] ?? false) {
            unset($workTime['trackable_type'], $workTime['trackable_id']);
        }

        return $this->getActions()[$name]::make($workTime);
    }
}
