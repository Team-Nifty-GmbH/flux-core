<?php

namespace FluxErp\Traits\Livewire\Calendar;

use FluxErp\Traits\HasCalendarUserSettings;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Livewire\Attributes\Renderless;

trait StoresCalendarSettings
{
    #[Renderless]
    public function storeSettings(mixed $data, ?string $setPath = null): void
    {
        if (! in_array(HasCalendarUserSettings::class, class_uses_recursive(auth()->user()))) {
            return;
        }

        $currentData = auth()->user()->getCalendarSettings(static::class)->value('settings') ?? [];

        if (! is_null($setPath)) {
            data_set($currentData, $setPath, $data);
            $setData = $currentData;
        } else {
            $data = Arr::wrap($data instanceof Arrayable ? $data->toArray() : $data);
            $setData = Arr::undot(
                array_merge(
                    Arr::dot($currentData),
                    Arr::dot($data)
                )
            );
        }

        auth()->user()
            ->calendarUserSettings()
            ->updateOrCreate(
                [
                    'cache_key' => static::class,
                    'component' => static::class,
                ],
                [
                    'settings' => $setData,
                ]
            );
    }

    #[Renderless]
    public function storeViewSettings(array $view): void
    {
        $this->storeSettings(data_get($view, 'type'), 'initialView');
    }

    #[Renderless]
    public function toggleEventSource(array $activeCalendars): void
    {
        $this->storeSettings(array_column($activeCalendars, 'publicId'), 'activeCalendars');
    }
}
