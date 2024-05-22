<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\FluxAction;
use FluxErp\Actions\WorkTime\CreateLockedWorkTime;
use FluxErp\Actions\WorkTime\DeleteWorkTime;
use FluxErp\Actions\WorkTime\UpdateLockedWorkTime;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Locked;

class LockedWorkTimeForm extends FluxForm
{
    #[Locked]
    public ?int $id = null;

    public ?int $user_id = null;

    public ?int $contact_id = null;

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

    public ?bool $is_billable = null;

    #[Locked]
    public bool $is_daily_work_time = false;

    #[Locked]
    public bool $is_locked = false;

    #[Locked]
    public bool $is_pause = false;

    public ?string $paused_time = null;

    public ?string $original_paused_time = null;

    protected function getActions(): array
    {
        return [
            'create' => CreateLockedWorkTime::class,
            'update' => UpdateLockedWorkTime::class,
            'delete' => DeleteWorkTime::class,
        ];
    }

    protected function makeAction(string $name, ?array $data = null): FluxAction
    {
        $workTime = $data ?? $this->toArray();
        if (! $workTime['trackable_type'] ?? false) {
            unset($workTime['trackable_type'], $workTime['trackable_id']);
        }

        if ($this->paused_time !== $this->original_paused_time) {
            if (is_null($this->paused_time)) {
                $workTime['paused_time_ms'] = 0;
            } else {
                if (preg_match('/^[0-9]+$/', $this->paused_time)) {
                    $this->paused_time = $this->paused_time . ':00';
                }

                if (preg_match('/^[0-9]+:[0-5][0-9]$/', $this->paused_time)) {
                    $exploded = explode(':', $this->paused_time);

                    $workTime['paused_time_ms'] = (int) bcmul(
                        bcadd(bcmul($exploded[0], 60), $exploded[1]),
                        60000
                    );
                }
            }
        }

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

        if ($this->paused_time_ms) {
            $minutes = ($pauseInMinutes = (int) ($this->paused_time_ms / 60000)) % 60;
            $hours = ($pauseInMinutes - $minutes) / 60;

            $this->paused_time = $hours . ':' . sprintf('%02d', $minutes);
            $this->original_paused_time = $this->paused_time;
        }
    }
}
