<?php

namespace FluxErp\Actions\WorkTime;

use FluxErp\Actions\DispatchableFluxAction;
use FluxErp\Actions\Order\CreateOrder;
use FluxErp\Actions\Order\UpdateOrder;
use FluxErp\Actions\OrderPosition\CreateOrderPosition;
use FluxErp\Models\Contact;
use FluxErp\Models\Order;
use FluxErp\Models\Product;
use FluxErp\Models\Warehouse;
use FluxErp\Models\WorkTime;
use FluxErp\Rulesets\WorkTime\CreateOrdersFromWorkTimesRuleset;
use FluxErp\Support\Collection\OrderCollection;
use Illuminate\Validation\ValidationException;

class CreateOrdersFromWorkTimes extends DispatchableFluxAction
{
    public static function getRulesets(): string|array
    {
        return CreateOrdersFromWorkTimesRuleset::class;
    }

    public static function models(): array
    {
        return [WorkTime::class, Order::class];
    }

    public function performAction(): OrderCollection
    {
        $createdOrderIds = [];
        $product = resolve_static(Product::class, 'query')
            ->whereKey($this->getData('product_id'))
            ->first();

        $roundMs = bcmul($this->getData('round_to_minute'), 60 * 1000);

        $selectedIds = array_column($this->getData('work_times'), 'id');

        $contacts = resolve_static(Contact::class, 'query')
            ->withWhereHas('workTimes', function ($query) use ($selectedIds) {
                $query->whereKey($selectedIds)
                    ->where('is_locked', true)
                    ->where('is_daily_work_time', false)
                    ->where('total_time_ms', '>', 0)
                    ->whereNull('order_position_id')
                    ->orderBy('is_billable', 'desc')
                    ->orderBy('started_at', 'desc')
                    ->when(
                        ! $this->getData('add_non_billable_work_times'),
                        fn ($query) => $query->where('is_billable', true)
                    );
            })
            ->with('invoiceAddress.language:id,language_code')
            ->get(['id', 'client_id', 'invoice_address_id']);

        foreach ($contacts as $contact) {
            if ($contact->workTimes->isEmpty()) {
                continue;
            }

            $order = CreateOrder::make([
                'client_id' => $contact->client_id,
                'contact_id' => $contact->getKey(),
                'order_type_id' => $this->getData('order_type_id'),
            ])
                ->validate()
                ->execute();
            $createdOrderIds[] = $order->getKey();

            $smallestStartedAt = null;
            $greatestEndedAt = null;
            foreach ($contact->workTimes as $workTime) {
                if ($this->getData('round') == 'ceil') {
                    $time = bcmul(bcceil(bcdiv($workTime->total_time_ms, $roundMs)), $roundMs);
                } elseif ($this->getData('round') == 'floor') {
                    $time = bcmul(bcfloor(bcdiv($workTime->total_time_ms, $roundMs)), $roundMs);
                } else {
                    $time = bcmul(bcround(bcdiv($workTime->total_time_ms, 60000)), 60000);
                }

                $billingAmount = bcround($product->time_unit_enum->convertFromMilliseconds($time), 2);

                try {
                    $prefix = ($workTime->workTimeType?->name
                        ? __('Type') . ': ' . $workTime->workTimeType->name . '<br/>'
                        : ''
                    );
                    $description = $prefix
                        . __('Date') . ': '
                        . $workTime->started_at
                            ->locale($contact->invoiceAddress->language->language_code)
                            ->isoFormat('L')
                        . '<br/>'
                        . __('User') . ': ' . $workTime->user->name
                        . '<br/><br/>'
                        . $workTime->description;

                    $orderPosition = CreateOrderPosition::make([
                        'name' => $workTime->name,
                        'description' => $description,
                        'warehouse_id' => Warehouse::default()?->getKey(),
                        'order_id' => $order->getKey(),
                        'product_id' => $product->getKey(),
                        'amount' => $billingAmount,
                        'discount_percentage' => ! $workTime->is_billable ? 1 : null,
                    ])->validate()->execute();
                } catch (ValidationException) {
                    continue;
                }

                try {
                    UpdateLockedWorkTime::make([
                        'id' => $workTime->getKey(),
                        'order_position_id' => $orderPosition->getKey(),
                    ])->validate()->execute();
                } catch (ValidationException) {
                    continue;
                }

                // Check and update the smallest started_at
                if (is_null($smallestStartedAt) || $workTime->started_at->lt($smallestStartedAt)) {
                    $smallestStartedAt = $workTime->started_at->startOfDay();
                }

                // Check and update the greatest ended_at
                if (is_null($greatestEndedAt) || $workTime->ended_at->gt($greatestEndedAt)) {
                    $greatestEndedAt = $workTime->ended_at->startOfDay();
                }
            }

            if ($smallestStartedAt->lt($greatestEndedAt)) {
                try {
                    UpdateOrder::make([
                        'id' => $order->getKey(),
                        'system_delivery_date' => $smallestStartedAt,
                        'system_delivery_date_end' => ($greatestEndedAt ?? now())->format('Y-m-d'),
                    ])->validate()->execute();
                } catch (ValidationException) {
                    continue;
                }
            }

            $order->calculatePrices()->save();
        }

        return resolve_static(Order::class, 'query')
            ->whereKey($createdOrderIds)
            ->get();
    }
}
