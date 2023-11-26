<?php

namespace FluxErp\Livewire;

use FluxErp\Livewire\Forms\WorkTimeForm;
use FluxErp\Models\WorkTimeType;
use FluxErp\Traits\Trackable;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Renderless;
use Livewire\Component;
use Spatie\ModelInfo\ModelInfo;
use Spatie\Permission\Exceptions\UnauthorizedException;
use WireUi\Traits\Actions;

class WorkTime extends Component
{
    use Actions;

    public WorkTimeForm $workTime;

    public WorkTimeForm $dailyWorkTime;

    public WorkTimeForm $dailyWorkTimePause;

    public array $activeWorkTimes = [];

    public function mount(): void
    {
        $this->activeWorkTimes = \FluxErp\Models\WorkTime::query()
            ->with('workTimeType:id,name')
            ->where('user_id', auth()->id())
            ->where('is_daily_work_time', false)
            ->where('is_locked', false)
            ->get()
            ->toArray();

        $this->dailyWorkTime->fill(\FluxErp\Models\WorkTime::query()
            ->where('user_id', auth()->id())
            ->where('is_daily_work_time', true)
            ->where('is_locked', false)
            ->first() ?? []);
    }

    public function render(): Factory|Application|View
    {
        return view('flux::livewire.work-time', [
            'workTimeTypes' => WorkTimeType::query()
                ->select(['id', 'name'])
                ->get()
                ->toArray(),
            'trackableTypes' => model_info_all()
                ->filter(fn (ModelInfo $modelInfo) => in_array(Trackable::class, $modelInfo->traits->toArray()))
                ->map(fn (ModelInfo $modelInfo) => $modelInfo->class)
                ->toArray(),
        ]);
    }

    #[Renderless]
    public function save(): bool
    {
        $isNew = is_null($this->workTime->id);
        try {
            $this->workTime->save();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        if ($isNew) {
            $this->activeWorkTimes[] = $this->workTime->toArray();
            $this->workTime->reset();
        }

        return true;
    }

    #[Renderless]
    public function toggleWorkDay(bool $start): void
    {
        if ($start) {
            $this->dailyWorkTime->fill([
                'user_id' => auth()->id(),
                'started_at' => now()->toDateTimeString(),
                'name' => 'Workday',
                'is_daily_work_time' => true,
            ]);
        } else {
            $this->dailyWorkTime->ended_at = now()->toDateTimeString();
            $this->dailyWorkTime->is_locked = true;

            $this->reset('activeWorkTimes');
            $this->dailyWorkTimePause->reset();
        }

        $this->dailyWorkTime->save();

        if (! $start) {
            $this->dailyWorkTime->reset();
        }
    }

    #[Renderless]
    public function togglePauseWorkDay(bool $start): void
    {
        if ($start) {
            $this->dailyWorkTimePause->fill([
                'user_id' => auth()->id(),
                'started_at' => now()->toDateTimeString(),
                'name' => 'Pause',
                'is_daily_work_time' => true,
                'is_pause' => true,
            ]);
        } else {
            $this->dailyWorkTimePause->ended_at = now()->toDateTimeString();
            $this->dailyWorkTimePause->is_locked = true;
        }

        $this->dailyWorkTimePause->save();

        if (! $start) {
            $this->dailyWorkTimePause->reset();
        }
    }

    #[Renderless]
    public function stop(\FluxErp\Models\WorkTime $workTime): bool
    {
        $this->workTime->fill($workTime);
        $this->workTime->ended_at = now()->toDateTimeString();
        $this->workTime->is_locked = true;

        if ($save = $this->save()) {
            $this->activeWorkTimes = Arr::keyBy($this->activeWorkTimes, 'id');
            unset($this->activeWorkTimes[$workTime->id]);
            $this->activeWorkTimes = array_values($this->activeWorkTimes);
        }

        return $save;
    }

    #[Renderless]
    public function pause(\FluxErp\Models\WorkTime $workTime): bool
    {
        $this->workTime->fill($workTime);
        $this->workTime->ended_at = now()->toDateTimeString();

        if ($this->save()) {
            $this->activeWorkTimes = Arr::keyBy($this->activeWorkTimes, 'id');
            $this->activeWorkTimes[$workTime->id] = $this->workTime->toArray();
            $this->activeWorkTimes = array_values($this->activeWorkTimes);

            return true;
        }

        return false;
    }

    #[Renderless]
    public function continue(\FluxErp\Models\WorkTime $workTime): bool
    {
        $this->workTime->fill($workTime);
        $this->workTime->ended_at = null;

        if ($this->save()) {
            $this->activeWorkTimes = Arr::keyBy($this->activeWorkTimes, 'id');
            $this->activeWorkTimes[$workTime->id] = $this->workTime->toArray();
            $this->activeWorkTimes = array_values($this->activeWorkTimes);

            return true;
        }

        return false;
    }

    public function lala(array $payload)
    {
        $this->workTime->fill($payload['model']);
    }
}
