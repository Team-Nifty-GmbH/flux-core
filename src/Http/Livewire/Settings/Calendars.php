<?php

namespace FluxErp\Http\Livewire\Settings;

use FluxErp\Models\Calendar;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Arr;
use Livewire\Component;

class Calendars extends Component
{
    public array $calendars = [];

    public bool $editModal = false;

    protected $listeners = [
        'updatedCalendar',
    ];

    public function mount(): void
    {
        $calendars = Calendar::query()
            ->whereNull('parent_id')
            ->whereNull('user_id')
            ->with(['children.parent', 'user', 'children.user'])
            ->get()
            ->toArray();

        $this->toFlatTree($calendars, 'calendars');
    }

    public function updatedCalendar()
    {
        $this->calendars = [];

        $this->mount();
    }

    public function render(): View|Factory|Application
    {
        return view('flux::livewire.settings.calendars');
    }

    /**
     * @param int|null $calendarId
     */
    public function showEditModal(int|null $calendarId = null): void
    {
        $this->editModal = true;
        $this->emitTo('calendar-edit', 'show', $calendarId, $this->calendars);

        $this->skipRender();
    }

    public function save()
    {
        $this->emitTo('calendar-edit', 'save');

        $this->skipRender();
    }

    private function toFlatTree(array $array, string $name, $slug = null): void
    {
        foreach ($array as $item) {
            $sanitized = Arr::only($item, ['id', 'name', 'event_component', 'color', 'module', 'user']);
            $sanitized['slug'] = $slug;
            $this->{$name}[] = $sanitized;

            if ($item['children'] ?? false) {
                $this->toFlatTree($item['children'], $name, $slug . '-' . $item['id']);
            }
        }
    }
}
