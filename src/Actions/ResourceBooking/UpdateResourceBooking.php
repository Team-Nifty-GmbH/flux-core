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
            ->whereKey($this->getData('id'))
            ->first();

        $this->data['resource_id'] ??= $booking?->resource_id;
        $this->data['start'] ??= $booking?->start?->toDateTimeString();
        $this->data['end'] ??= $booking?->end?->toDateTimeString();

        $this->addRules([
            'start' => [
                app(ResourceAvailable::class, [
                    'resourceId' => $this->getData('resource_id'),
                    'start' => $this->getData('start'),
                    'end' => $this->getData('end'),
                    'ignoreId' => $this->getData('id'),
                ]),
            ],
        ]);
    }
}
