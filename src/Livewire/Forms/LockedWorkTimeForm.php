<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\FluxAction;
use FluxErp\Actions\WorkTime\DeleteWorkTime;
use FluxErp\Actions\WorkTime\UpdateLockedWorkTime;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Locked;

class LockedWorkTimeForm extends FluxForm
{
    #[Locked]
    public ?int $id = null;

    public ?int $contact_id = null;

    public ?int $user_id = null;

    public ?int $order_position_id = null;

    public ?int $parent_id = null;

    public ?int $work_time_type_id = null;

    public ?string $trackable_type = null;

    public ?int $trackable_id = null;

    public ?string $started_at = null;

    public ?string $ended_at = null;

    public ?int $paused_time_ms = null;

    public ?string $name = null;

    public ?string $description = null;

    #[Locked]
    public bool $is_daily_work_time = false;

    #[Locked]
    public bool $is_locked = false;

    #[Locked]
    public bool $is_pause = false;

    protected function getActions(): array
    {
        return [
            'update' => UpdateLockedWorkTime::class,
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

        $workTime['started_at'] = $workTime['started_at']
            ? Carbon::parse($workTime['started_at'])->format('Y-m-d H:i:s')
            : null;
        $workTime['ended_at'] = $workTime['ended_at']
            ? Carbon::parse($workTime['ended_at'])->format('Y-m-d H:i:s')
            : null;
        $workTime['is_locked'] = (bool) $workTime['ended_at'];

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
