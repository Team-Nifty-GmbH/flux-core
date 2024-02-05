<?php

namespace FluxErp\Listeners\Order;

use FluxErp\Actions\Commission\CreateCommission;
use FluxErp\Models\Order;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;
use Spatie\MediaLibrary\MediaCollections\Events\MediaHasBeenAddedEvent;

class OrderInvoiceAddedSubscriber
{
    public function handle(MediaHasBeenAddedEvent $event): void
    {
        if ($event->media->collection_name !== 'invoice'
            || $event->media->model_type !== Order::class
        ) {
            return;
        }

        $order = $event->media->model;

        $agent = $order->agent;
        if (! $agent) {
            return;
        }

        // If user has no commission rates return
        $commissionRates = $agent->commissionRates()
            ->where(function (Builder $query) use ($order) {
                $query->where('contact_id', $order->addressInvoice->contact_id)
                    ->orWhereNull('contact_id');
            })
            ->orderBy('commission_rate', 'DESC')
            ->get();

        if (! $commissionRates) {
            return;
        }

        $contactCommissionRates = $commissionRates->where('contact_id', $order->addressInvoice->contact_id);
        $defaultCommissionRates = $commissionRates->whereNull('contact_id');
        $defaultCommissionRateByContact = $contactCommissionRates
            ->whereNull('category_id')
            ->whereNull('product_id')
            ->first();

        $orderPositions = $order->orderPositions()
            ->where('is_free_text', false)
            ->where('is_bundle_position', false)
            ->get();

        foreach ($orderPositions as $orderPosition) {
            $commissionRateId = null;
            if (! $orderPosition->product_id
                && $defaultCommissionRateByContact
            ) {
                $commissionRateId = $defaultCommissionRateByContact->id;
            } elseif ($orderPosition->product_id) {
                switch (true) {
                    case $commissionRateId = $contactCommissionRates
                    ->where('product_id', $orderPosition->product_id)
                    ->first()
                    ?->id:
                    case $commissionRateId = $contactCommissionRates
                    ->whereIn(
                        'category_id',
                        $orderPosition->product->categories()->pluck('id')->toArray()
                    )
                    ->first()
                    ?->id:
                    case $commissionRateId = $defaultCommissionRates
                    ->where('product_id', $orderPosition->product_id)
                    ->first()
                    ?->id:
                    case $commissionRateId = $defaultCommissionRates
                    ->whereIn(
                        'category_id',
                        $orderPosition->product->categories()->pluck('id')->toArray()
                    )
                    ->first()
                    ?->id:
                    case $commissionRateId = $defaultCommissionRateByContact?->id:
                    case $commissionRateId = $defaultCommissionRates
                    ->whereNull('category_id')
                    ->whereNull('product_id')
                    ->first()
                    ?->id:
                        break;
                    default:
                        $commissionRateId = null;
                        break;
                }
            }

            if ($commissionRateId) {
                try {
                    CreateCommission::make([
                        'user_id' => $agent->id,
                        'commission_rate_id' => $commissionRateId,
                        'order_position_id' => $orderPosition->id,
                    ])->validate()->execute();
                } catch (ValidationException) {
                }
            }
        }
    }

    public function subscribe(): array
    {
        return [
            MediaHasBeenAddedEvent::class => 'handle',
        ];
    }
}
