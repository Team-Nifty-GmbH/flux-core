<?php

namespace FluxErp\Actions\RebateAgreement;

use FluxErp\Actions\FluxAction;
use FluxErp\Actions\Order\CreateOrder;
use FluxErp\Actions\OrderPosition\CreateOrderPosition;
use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Models\Language;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use FluxErp\Models\RebateAgreement;
use FluxErp\Rulesets\RebateAgreement\SettleRebateAgreementRuleset;
use FluxErp\Support\Calculation\RebateCalculator;
use Illuminate\Support\Number;
use Illuminate\Validation\ValidationException;

class SettleRebateAgreement extends FluxAction
{
    protected ?array $calculation = null;

    protected ?int $currencyId = null;

    public static function models(): array
    {
        return [RebateAgreement::class];
    }

    protected function getRulesets(): string|array
    {
        return SettleRebateAgreementRuleset::class;
    }

    public function getCalculation(): ?array
    {
        return $this->calculation;
    }

    public function performAction(): Order
    {
        $rebateAgreement = resolve_static(RebateAgreement::class, 'query')
            ->whereKey($this->getData('id'))
            ->lockForUpdate()
            ->first();

        if ($rebateAgreement->rebateOrder()->exists()) {
            throw ValidationException::withMessages([
                'id' => ['The rebate agreement is already settled.'],
            ])
                ->errorBag('settleRebateAgreement');
        }

        $order = CreateOrder::make([
            'contact_id' => $rebateAgreement->contact_id,
            'currency_id' => $this->currencyId,
            'order_type_id' => $rebateAgreement->order_type_id,
        ])
            ->validate()
            ->execute();

        $name = $this->getPositionName($rebateAgreement, $order);

        foreach (data_get($this->calculation, 'positions') ?? [] as $position) {
            CreateOrderPosition::make([
                'order_id' => $order->getKey(),
                'vat_rate_id' => data_get($position, 'vat_rate_id'),
                'amount' => 1,
                'unit_price' => data_get($position, 'total_net_price'),
                'name' => $name,
                'is_net' => true,
            ])
                ->validate()
                ->execute();
        }

        $order->refresh()
            ->calculatePrices()
            ->save();

        $rebateAgreement->rebate_order_id = $order->getKey();
        $rebateAgreement->settled_at = now();
        $rebateAgreement->save();

        return $order->refresh();
    }

    protected function getPositionName(RebateAgreement $rebateAgreement, Order $order): string
    {
        $languageCode = $order->language?->language_code
            ?? resolve_static(Language::class, 'default')?->language_code;

        return __(
            'Rebate :percentage for :start until :end',
            [
                'percentage' => Number::percentage(
                    bcmul(data_get($this->calculation, 'percentage'), 100),
                    maxPrecision: 2,
                    locale: $languageCode
                ),
                'start' => $rebateAgreement->period_start->locale($languageCode)->isoFormat('L'),
                'end' => $rebateAgreement->period_end->locale($languageCode)->isoFormat('L'),
            ],
            $languageCode
        );
    }

    protected function validateData(): void
    {
        parent::validateData();

        $rebateAgreement = resolve_static(RebateAgreement::class, 'query')
            ->whereKey($this->getData('id'))
            ->first();
        $errors = [];

        if ($rebateAgreement->rebateOrder()->exists()) {
            $errors['id'][] = 'The rebate agreement is already settled.';
        }

        if (
            ! resolve_static(OrderType::class, 'query')
                ->whereKey($rebateAgreement->order_type_id)
                ->where('order_type_enum', OrderTypeEnum::Refund)
                ->where('is_active', true)
                ->exists()
        ) {
            $errors['order_type_id'][] = 'The order type must be an active refund order type.';
        }

        $calculator = RebateCalculator::make($rebateAgreement);
        $currencyIds = $calculator->getCurrencyIds();
        $calculation = null;

        if ($currencyIds->count() > 1) {
            $errors['id'][] = 'The orders within the period use different currencies.';
        } else {
            $calculation = $calculator->calculate();
            $positions = collect(data_get($calculation, 'positions'));

            if ($positions->isEmpty()) {
                $errors['id'][] = 'The volume does not reach any tier of this rebate agreement.';
            } elseif ($positions->whereNull('vat_rate_id')->isNotEmpty()) {
                $errors['id'][] = 'Revenue without a vat rate cannot be settled automatically.';
            }
        }

        if ($errors) {
            throw ValidationException::withMessages($errors)
                ->errorBag('settleRebateAgreement');
        }

        $this->currencyId = $currencyIds->first();
        $this->calculation = $calculation;
    }
}
