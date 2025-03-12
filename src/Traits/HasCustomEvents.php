<?php

namespace FluxErp\Traits;

use FluxErp\Models\CustomEvent;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Collection;

trait HasCustomEvents
{
    public function customEvents(): MorphMany
    {
        return $this->morphMany(CustomEvent::class, 'model')
            ->setQuery(
                CustomEvent::query()
                    ->where('model_type', self::class)
                    ->whereNull('model_id')
                    ->toBase()
                    ->orWhere(function (\Illuminate\Database\Query\Builder $query): void {
                        $query->where('model_type', $this->getMorphClass())
                            ->where('model_id', $this->getKey());
                    })
            );
    }

    public function getCustomEventsAttribute(): Collection
    {
        $customEvents = $this->customEvents()->get();

        foreach ($this->getRelatedCustomEvents() as $relatedCustomEvent) {
            if (! method_exists($this, $relatedCustomEvent)) {
                continue;
            }

            foreach ($this->{$relatedCustomEvent}()?->get() ?? [] as $model) {
                $customEvents = $customEvents->merge($model?->customEvents()?->get() ?? []);
            }
        }

        return $customEvents;
    }

    public function getRelatedCustomEvents(): array
    {
        return $this->relatedCustomEvents ?? [];
    }
}
