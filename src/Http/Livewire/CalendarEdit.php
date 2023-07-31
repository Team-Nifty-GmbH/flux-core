<?php

namespace FluxErp\Http\Livewire;

use FluxErp\Http\Requests\CreateCalendarRequest;
use FluxErp\Http\Requests\UpdateCalendarRequest;
use FluxErp\Models\Calendar;
use FluxErp\Models\User;
use FluxErp\Services\CalendarService;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory as FactoryAlias;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Arr;
use Livewire\Component;

class CalendarEdit extends Component
{
    public array $selectedCalendar = [
        'id' => null,
        'color' => null,
        'parent_id' => null,
        'user_id' => null,
        'module' => null,
    ];

    public array $bluePrint = [];

    public array $availableModules = [];

    public array $parentOptions = [];

    public bool $deletable = false;

    public array $users = [];

    public bool $modal = false;

    protected $listeners = [
        'save',
        'show',
    ];

    public bool $editModal = false;

    protected function getRules(): array
    {
        return Arr::prependKeysWith(
            ($this->selectedCalendar['id'] ?? false)
                ? (new UpdateCalendarRequest())->rules()
                : (new CreateCalendarRequest())->rules(),
            'selectedCalendar.');
    }

    public function mount(): void
    {
        $this->availableModules = get_subclasses_of(
            \FluxErp\Http\Livewire\Features\Calendar\Calendar::class,
            'FluxErp\Http\Livewire'
        );

        $this->users = User::all()->toArray();
    }

    public function boot(): void
    {
        $this->parentOptions = Calendar::query()
            ->whereNull('user_id')
            ->with(['children.parent', 'user', 'children.user'])
            ->get()
            ->toArray();
    }

    public function render(): View|FactoryAlias|Application
    {
        return view('flux::livewire.calendar-edit');
    }

    public function show(int $id = null, ?array $parentOptions = []): void
    {
        if (! is_null($parentOptions)) {
            $this->parentOptions = $parentOptions;
        }

        if (! $id) {
            $this->selectedCalendar = [
                'id' => null,
                'parent_id' => null,
                'user_id' => null,
                'module' => null,
            ];
        } else {
            $this->selectedCalendar = Calendar::query()
                ->where('id', $id)
                ->with('children.parent')
                ->first()
                ->toArray();
        }

        $this->deletable = true;

        $this->editModal = true;

        $this->skipRender();
    }

    public function save(): void
    {
        $this->selectedCalendar = array_merge($this->selectedCalendar, $this->bluePrint);

        $validated = $this->validate();

        $function = ($this->selectedCalendar['id'] ?? false) ? 'update' : 'create';

        $response = (new CalendarService())->{$function}($validated['selectedCalendar']);

        $this->editModal = false;

        $this->emitUp('updatedCalendar');
        $this->skipRender();
    }

    public function delete(): void
    {
        if (! user_can('api.calendars.{id}.delete')) {
            return;
        }

        Calendar::query()->whereKey($this->selectedCalendar['id'])->delete();
        $this->emitUp('updatedCalendar');

        $this->skipRender();
    }
}
