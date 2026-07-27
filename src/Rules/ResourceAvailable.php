<?php

namespace FluxErp\Rules;

use Closure;
use FluxErp\Models\Resource;
use FluxErp\Models\ResourceBooking;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Throwable;

class ResourceAvailable implements ValidationRule
{
    public function __construct(
        protected ?int $resourceId,
        protected ?string $start,
        protected ?string $end,
        protected ?int $ignoreId = null,
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $this->resourceId || ! $this->start || ! $this->end) {
            return;
        }

        try {
            $start = Carbon::parse($this->start)->toDateTimeString();
            $end = Carbon::parse($this->end)->toDateTimeString();
        } catch (Throwable) {
            return;
        }

        $resource = resolve_static(Resource::class, 'query')
            ->whereKey($this->resourceId)
            ->first(['id', 'allow_overbooking']);

        if (! $resource || $resource->allow_overbooking) {
            return;
        }

        $conflict = resolve_static(ResourceBooking::class, 'query')
            ->where('resource_id', $this->resourceId)
            ->when($this->ignoreId, fn (Builder $query) => $query->whereKeyNot($this->ignoreId))
            ->where('start', '<', $end)
            ->where('end', '>', $start)
            ->exists();

        if ($conflict) {
            $fail('validation.resource_unavailable')->translate();
        }
    }
}
