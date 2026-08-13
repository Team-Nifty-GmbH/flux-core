<?php

namespace FluxErp\Actions\PaymentRun;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Order;
use FluxErp\Models\PaymentRun;
use FluxErp\Models\PaymentRunPosition;
use FluxErp\Rulesets\PaymentRun\CreatePaymentRunRuleset;
use FluxErp\States\Order\PaymentState\InOpenPaymentRun;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class CreatePaymentRun extends FluxAction
{
    public static function models(): array
    {
        return [PaymentRun::class];
    }

    protected function getRulesets(): string|array
    {
        return CreatePaymentRunRuleset::class;
    }

    public function performAction(): Model
    {
        $positions = Arr::pull($this->data, 'positions');
        Arr::pull($this->data, 'orders');

        $paymentRun = app(PaymentRun::class, ['attributes' => $this->data]);
        $paymentRun->save();

        $orderIds = [];

        foreach ($positions as $positionData) {
            $orders = Arr::pull($positionData, 'orders');
            $amount = array_reduce(
                $orders,
                fn (string $carry, array $order) => bcadd($carry, (string) data_get($order, 'amount'), 9),
                '0'
            );

            $position = app(PaymentRunPosition::class, [
                'attributes' => array_merge($positionData, [
                    'payment_run_id' => $paymentRun->getKey(),
                    'amount' => $amount,
                ]),
            ]);
            $position->save();

            $position->end_to_end_id = 'PR' . $paymentRun->getKey() . '-' . $position->getKey();
            $position->save();

            foreach ($orders as $order) {
                $position->orders()->attach(data_get($order, 'order_id'), [
                    'payment_run_id' => $paymentRun->getKey(),
                    'amount' => data_get($order, 'amount'),
                ]);

                $orderIds[] = data_get($order, 'order_id');
            }
        }

        resolve_static(Order::class, 'query')
            ->whereIntegerInRaw('id', $orderIds)
            ->each(function (Order $order): void {
                if ($order->payment_state->canTransitionTo(InOpenPaymentRun::class)) {
                    $order->payment_state->transitionTo(InOpenPaymentRun::class);
                }
            });

        return $paymentRun->fresh();
    }

    protected function prepareForValidation(): void
    {
        if (! data_get($this->data, 'positions') && data_get($this->data, 'orders')) {
            $flatOrders = Arr::pull($this->data, 'orders');

            $models = resolve_static(Order::class, 'query')
                ->with('contactBankConnection:id,iban,bic,account_holder')
                ->whereIntegerInRaw('id', array_column($flatOrders, 'order_id'))
                ->get()
                ->keyBy(fn (Order $order) => $order->getKey());

            $this->data['positions'] = array_map(
                function (array $order) use ($models): array {
                    $model = $models->get(data_get($order, 'order_id'));

                    return [
                        'contact_id' => $model?->contact_id,
                        'iban' => $model?->iban ?: $model?->contactBankConnection?->iban,
                        'bic' => $model?->bic ?: $model?->contactBankConnection?->bic,
                        'account_holder' => $model?->account_holder
                            ?: $model?->contactBankConnection?->account_holder,
                        'purpose' => $model?->invoice_number,
                        'orders' => [$order],
                    ];
                },
                $flatOrders
            );
        }
    }
}
