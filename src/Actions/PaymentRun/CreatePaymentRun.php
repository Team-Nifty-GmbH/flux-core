<?php

namespace FluxErp\Actions\PaymentRun;

use FluxErp\Actions\FluxAction;
use FluxErp\Enums\PaymentRunTypeEnum;
use FluxErp\Models\Order;
use FluxErp\Models\PaymentRun;
use FluxErp\Models\PaymentRunPosition;
use FluxErp\Rulesets\PaymentRun\CreatePaymentRunRuleset;
use FluxErp\States\Order\PaymentState\InOpenPaymentRun;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

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

        $paymentRun = app(PaymentRun::class, ['attributes' => $this->data]);
        $paymentRun->save();

        $orderIds = [];

        foreach ($positions as $positionData) {
            $orders = Arr::pull($positionData, 'orders');

            $position = app(PaymentRunPosition::class, [
                'attributes' => array_merge($positionData, [
                    'payment_run_id' => $paymentRun->getKey(),
                    'amount' => 0,
                ]),
            ]);
            $position->save();

            foreach ($orders as $order) {
                $position->orders()->attach(data_get($order, 'order_id'), [
                    'payment_run_id' => $paymentRun->getKey(),
                    'amount' => data_get($order, 'amount'),
                ]);
            }

            $position->end_to_end_id = 'PR' . $paymentRun->getKey() . '-' . $position->getKey();
            $position->refreshFromOrders();

            $orderIds = array_merge($orderIds, array_column($orders, 'order_id'));
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
            $this->data['positions'] = array_map(
                fn (array $order): array => ['orders' => [$order]],
                Arr::pull($this->data, 'orders')
            );
        }

        $positions = array_filter((array) data_get($this->data, 'positions'), 'is_array');

        $models = resolve_static(Order::class, 'query')
            ->whereKey(array_filter(data_get($positions, '*.orders.0.order_id') ?? []))
            ->with(['contactBankConnection:id,iban,bic,account_holder', 'addressInvoice'])
            ->get()
            ->keyBy(fn (Order $order) => $order->getKey());

        foreach ($positions as $index => $position) {
            $model = $models->get(data_get($position, 'orders.0.order_id'));

            $this->data['positions'][$index] = array_merge(
                ['contact_id' => $model?->contact_id],
                $model?->paymentRecipient() ?? [],
                $position
            );
        }
    }

    protected function validateData(): void
    {
        parent::validateData();

        $paymentRunTypeEnum = $this->getData('payment_run_type_enum');
        $paymentRunTypeEnum = $paymentRunTypeEnum instanceof PaymentRunTypeEnum
            ? $paymentRunTypeEnum
            : PaymentRunTypeEnum::from($paymentRunTypeEnum);

        $errors = [];
        foreach ($this->getData('positions') ?? [] as $index => $position) {
            $orders = data_get($position, 'orders') ?? [];
            $amount = bcsum(array_column($orders, 'amount'));

            if (bccomp($amount, '0', 2) !== $paymentRunTypeEnum->expectedSign()) {
                $errors['positions.' . $index . '.orders'] = [
                    'A payment run position must not point against the direction of the run.',
                ];
            }
        }

        if ($errors) {
            throw ValidationException::withMessages($errors)
                ->errorBag('createPaymentRun');
        }
    }
}
