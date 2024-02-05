<?php

namespace FluxErp\Livewire\Forms;

use Carbon\Carbon;
use FluxErp\Actions\FluxAction;
use FluxErp\Actions\WorkTime\CreateWorkTime;
use FluxErp\Actions\WorkTime\DeleteWorkTime;
use FluxErp\Actions\WorkTime\UpdateWorkTime;
use Livewire\Attributes\Locked;

class WorkTimeForm extends FluxForm
{
    #[Locked]
    public ?int $id = null;

    public ?int $user_id = null;

    public ?int $contact_id = null;

    #[Locked]
    public ?int $order_position_id = null;

    #[Locked]
    public ?int $parent_id = null;

    public ?int $work_time_type_id = null;

    public ?string $trackable_type = null;

    public ?int $trackable_id = null;

    #[Locked]
    public ?string $started_at = null;

    #[Locked]
    public ?string $ended_at = null;

    #[Locked]
    public ?int $paused_time_ms = null;

    public ?string $name = null;

    public ?string $description = null;

    public ?bool $is_billable = null;

    #[Locked]
    public bool $is_daily_work_time = false;

    #[Locked]
    public bool $is_locked = false;

    #[Locked]
    public bool $is_pause = false;

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

    public function __toString(): string
    {
        return (string) $this->id;
    }

    public function fill($values): void
    {
        parent::fill($values);

        $this->started_at = $this->started_at ? Carbon::parse($this->started_at)->format('Y-m-d H:i:s') : null;
        $this->ended_at = $this->ended_at ? Carbon::parse($this->ended_at)->format('Y-m-d H:i:s') : null;
    }
}
