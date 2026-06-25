<?php

namespace FluxErp\Actions\ResourceBooking;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\ResourceBooking;
use FluxErp\Rules\ResourceAvailable;
use FluxErp\Rulesets\ResourceBooking\UpdateResourceBookingRuleset;
use Illuminate\Database\Eloquent\Model;

class UpdateResourceBooking extends FluxAction
{
    public static function models(): array
    {
        return [ResourceBooking::class];
    }

    protected function getRulesets(): string|array
    {
        return UpdateResourceBookingRuleset::class;
    }

    public function performAction(): Model
    {
        $booking = resolve_static(ResourceBooking::class, 'query')
            ->whereKey($this->data['id'])
            ->first();

        $booking->fill($this->data);
        $booking->save();

        return $booking->withoutRelations()->fresh();
    }

    protected function prepareForValidation(): void
    {
        $booking = resolve_static(ResourceBooking::class, 'query')
            ->whereKey(data_get($this->data, 'id'))
            ->first();

        $this->mergeRules([
            'start' => [
                'sometimes',
                'required',
                'date',
                app(ResourceAvailable::class, [
                    'resourceId' => data_get($this->data, 'resource_id', $booking?->resource_id),
                    'start' => data_get($this->data, 'start', $booking?->start?->toDateTimeString()),
                    'end' => data_get($this->data, 'end', $booking?->end?->toDateTimeString()),
                    'ignoreId' => data_get($this->data, 'id'),
                ]),
            ],
        ]);
    }
}
