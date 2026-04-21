<?php

namespace FluxErp\Actions\WorkTime;

use FluxErp\Actions\DispatchableFluxAction;
use FluxErp\Actions\Order\CreateOrder;
use FluxErp\Actions\Order\UpdateOrder;
use FluxErp\Actions\OrderPosition\CreateOrderPosition;
use FluxErp\Models\Contact;
use FluxErp\Models\Language;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use FluxErp\Models\Product;
use FluxErp\Models\Tenant;
use FluxErp\Models\Warehouse;
use FluxErp\Models\WorkTime;
use FluxErp\Rulesets\WorkTime\CreateOrdersFromWorkTimesRuleset;
use FluxErp\Support\Calculation\Rounding;
use FluxErp\Support\Collection\OrderCollection;
use Illuminate\Validation\ValidationException;

class CreateOrdersFromWorkTimes extends DispatchableFluxAction
{
    public static function models(): array
    {
        return [WorkTime::class, Order::class];
    }

    protected function getRulesets(): string|array
    {
        return CreateOrdersFromWorkTimesRuleset::class;
    }

    public function performAction(): OrderCollection
    {
        $product = resolve_static(Product::class, 'query')
            ->whereKey($this->getData('product_id'))
            ->first();

        $roundMs = bcmul($this->getData('round_to_minute') ?? 1, 60 * 1000);
        $selectedIds = array_column($this->getData('work_times'), 'id');

        if ($this->getData('order_id')) {
            return $this->addPositionsToExistingOrder($product, $roundMs, $selectedIds);
        }

        return $this->createNewOrders($product, $roundMs, $selectedIds);
    }

    protected function addPositionsToExistingOrder(Product $product, string $roundMs, array $selectedIds): OrderCollection
    {
        $order = resolve_static(Order::class, 'query')
            ->with('contact.invoiceAddress.language:id,language_code')
            ->whereKey($this->getData('order_id'))
            ->first();

        $workTimes = resolve_static(WorkTime::class, 'query')
            ->whereKey($selectedIds)
            ->where('is_locked', true)
            ->where('is_daily_work_time', false)
            ->where('total_time_ms', '>', 0)
            ->whereNull('order_position_id')
            ->when(
                ! $this->getData('add_non_billable_work_times'),
                fn ($query) => $query->where('is_billable', true)
            )
            ->orderBy('is_billable', 'desc')
            ->orderBy('started_at', 'desc')
            ->get();

        $languageCode = $order->contact?->invoiceAddress?->language?->language_code
            ?? resolve_static(Language::class, 'default')?->language_code;

        $this->createPositionsForOrder($order, $workTimes, $product, $roundMs, $languageCode);

        return resolve_static(Order::class, 'query')
            ->whereKey($order->getKey())
            ->get();
    }

    protected function createNewOrders(Product $product, string $roundMs, array $selectedIds): OrderCollection
    {
        $createdOrderIds = [];

        $contacts = resolve_static(Contact::class, 'query')
            ->withWhereHas('workTimes', function ($query) use ($selectedIds): void {
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
            ->get(['id', 'invoice_address_id']);

        foreach ($contacts as $contact) {
            if ($contact->workTimes->isEmpty()) {
                continue;
            }

            $order = CreateOrder::make([
                'contact_id' => $contact->getKey(),
                'order_type_id' => $this->getData('order_type_id'),
                'tenant_id' => $this->getData('tenant_id'),
            ])
                ->validate()
                ->execute();
            $createdOrderIds[] = $order->getKey();

            $languageCode = $contact->invoiceAddress->language?->language_code
                ?? resolve_static(Language::class, 'default')?->language_code;

            $this->createPositionsForOrder($order, $contact->workTimes, $product, $roundMs, $languageCode);
        }

        return resolve_static(Order::class, 'query')
            ->whereKey($createdOrderIds)
            ->get();
    }

    protected function createPositionsForOrder(
        Order $order,
        $workTimes,
        Product $product,
        string $roundMs,
        ?string $languageCode
    ): void {
        $earliestStartedAt = null;
        $latestEndedAt = null;

        foreach ($workTimes as $workTime) {
            $time = Rounding::nearest(
                number: (int) $roundMs,
                value: $workTime->total_time_ms,
                precision: 0,
                mode: $this->getData('round')
            );
            $billingAmount = bcround($product->time_unit_enum->convertFromMilliseconds($time), 2);

            try {
                $prefix = ($workTime->workTimeType?->name
                    ? __('Type') . ': ' . $workTime->workTimeType->name . '<br/>'
                    : ''
                );
                $description = $prefix
                    . __('Date') . ': '
                    . $workTime->started_at
                        ->locale($languageCode)
                        ->isoFormat('L')
                    . '<br/>'
                    . __('User') . ': ' . $workTime->user->name
                    . '<br/><br/>'
                    . $workTime->description;

                $orderPosition = CreateOrderPosition::make([
                    'name' => $workTime->name,
                    'description' => $description,
                    'warehouse_id' => resolve_static(Warehouse::class, 'default')?->getKey(),
                    'order_id' => $order->getKey(),
                    'product_id' => $product->getKey(),
                    'amount' => $billingAmount,
                    'discount_percentage' => ! $workTime->is_billable ? 1 : null,
                ])
                    ->validate()
                    ->execute();
            } catch (ValidationException) {
                continue;
            }

            try {
                UpdateLockedWorkTime::make([
                    'id' => $workTime->getKey(),
                    'order_position_id' => $orderPosition->getKey(),
                ])
                    ->validate()
                    ->execute();
            } catch (ValidationException) {
                continue;
            }

            if (is_null($earliestStartedAt) || $workTime->started_at->lt($earliestStartedAt)) {
                $earliestStartedAt = $workTime->started_at->startOfDay();
            }

            if (is_null($latestEndedAt) || $workTime->ended_at->gt($latestEndedAt)) {
                $latestEndedAt = $workTime->ended_at->startOfDay();
            }
        }

        if ($earliestStartedAt?->lte($latestEndedAt)) {
            try {
                UpdateOrder::make([
                    'id' => $order->getKey(),
                    'system_delivery_date' => $earliestStartedAt,
                    'system_delivery_date_end' => ($latestEndedAt ?? now())->format('Y-m-d'),
                ])
                    ->validate()
                    ->execute();
            } catch (ValidationException) {
            }
        }

        $order->calculatePrices()->save();
    }

    protected function prepareForValidation(): void
    {
        $this->data['tenant_id'] ??= resolve_static(Tenant::class, 'default')?->getKey();
    }

    protected function validateData(): void
    {
        parent::validateData();

        if (! $this->getData('order_id')
            && resolve_static(OrderType::class, 'query')
                ->whereKey($this->getData('order_type_id'))
                ->whereHasTenant($this->getData('tenant_id'))
                ->doesntExist()
        ) {
            throw ValidationException::withMessages([
                'order_type_id' => ['Order Type not found on given tenant.'],
            ])
                ->errorBag('createOrdersFromWorkTimes');
        }
    }
}
