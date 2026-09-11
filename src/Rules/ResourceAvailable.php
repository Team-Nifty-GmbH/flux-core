<?php

namespace FluxErp\Rules;

use Closure;
use FluxErp\Models\Resource;
use FluxErp\Models\ResourceBooking;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Throwable;

class ResourceAvailable implements DataAwareRule, ValidationRule
{
    protected array $data = [];

    public function setData(array $data): static
    {
        $this->data = $data;

        return $this;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $resourceId = data_get($this->data, 'resource_id');

        if (! $resourceId || ! data_get($this->data, 'start') || ! data_get($this->data, 'end')) {
            return;
        }

        try {
            $start = Carbon::parse(data_get($this->data, 'start'))->toDateTimeString();
            $end = Carbon::parse(data_get($this->data, 'end'))->toDateTimeString();
        } catch (Throwable) {
            return;
        }

        $resource = resolve_static(Resource::class, 'query')
            ->whereKey($resourceId)
            ->first(['id', 'allow_overbooking']);

        if (! $resource || $resource->allow_overbooking) {
            return;
        }

        $conflict = resolve_static(ResourceBooking::class, 'query')
            ->where('resource_id', $resourceId)
            ->when(
                data_get($this->data, 'id'),
                fn (Builder $query, int $id): Builder => $query->whereKeyNot($id)
            )
            ->where('start', '<', $end)
            ->where('end', '>', $start)
            ->exists();

        if ($conflict) {
            $fail('validation.resource_unavailable')->translate();
        }
    }
}
