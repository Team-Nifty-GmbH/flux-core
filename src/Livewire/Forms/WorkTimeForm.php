<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\WorkTime\CreateWorkTime;
use FluxErp\Actions\WorkTime\UpdateWorkTime;
use Livewire\Attributes\Locked;
use Livewire\Form;

class WorkTimeForm extends Form
{
    #[Locked]
    public ?int $id = null;

    public ?int $contact_id = null;

    public ?int $user_id = null;

    #[Locked]
    public ?int $order_position_id = null;

    public ?int $work_time_type_id = null;

    public ?string $trackable_type = null;

    public ?int $trackable_id = null;

    #[Locked]
    public ?string $started_at = null;

    #[Locked]
    public ?string $ended_at = null;

    #[Locked]
    public ?int $paused_time = null;

    public ?string $name = null;

    public ?string $description = null;

    #[Locked]
    public bool $is_daily_work_time = false;

    #[Locked]
    public bool $is_locked = false;

    #[Locked]
    public bool $is_pause = false;

    public function save(): void
    {
        $this->user_id = $this->user_id ?? auth()->id();

        $workTime = $this->toArray();
        if (! $workTime['trackable_type'] ?? false) {
            unset($workTime['trackable_type'], $workTime['trackable_id']);
        }

        $action = $this->id
            ? UpdateWorkTime::make($workTime)
            : CreateWorkTime::make($workTime);

        $response = $action
            ->checkPermission()
            ->validate()
            ->execute();

        $this->fill($response);
    }

    public function __toString()
    {
        return (string) $this->id;
    }
}
