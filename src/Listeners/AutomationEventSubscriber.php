<?php

namespace FluxErp\Listeners;

use FluxErp\Models\Automation;
use Illuminate\Support\Facades\Bus;

class AutomationEventSubscriber
{
    public function handle(string $event, array $data): void
    {
        $automations = app(Automation::class)->query()
            ->where('event', $event)
            ->where('is_active', true)
            ->get();

        // Todo: Implement conditions and custom payload
        foreach ($automations as $automation) {
            $batch = array_map(fn ($action) => new $action($data[0]), $automation->actions);
            Bus::batch($batch)->dispatch();
        }
    }

    public function subscribe(): array
    {
        $automations = app(Automation::class)->query()
            ->where('is_active', true)
            ->pluck('event')
            ->map(fn ($event) => $event . '*')
            ->toArray();

        return array_fill_keys($automations, 'handle');
    }
}
