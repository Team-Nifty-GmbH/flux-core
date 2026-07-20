<?php

namespace FluxErp\Actions\RebateAgreement;

use FluxErp\Actions\FluxAction;
use FluxErp\Actions\Order\CreateOrder;
use FluxErp\Actions\OrderPosition\CreateOrderPosition;
use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Models\Currency;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use FluxErp\Models\RebateAgreement;
use FluxErp\Rulesets\RebateAgreement\SettleRebateAgreementRuleset;
use Illuminate\Support\Fluent;
use Illuminate\Support\Number;
use Illuminate\Validation\ValidationException;

class SettleRebateAgreement extends FluxAction
{
    public static function models(): array
    {
        return [RebateAgreement::class];
    }

    protected function getRulesets(): string|array
    {
        return SettleRebateAgreementRuleset::class;
    }

    public function performAction(): Order
    {
        $rebateAgreement = resolve_static(RebateAgreement::class, 'query')
            ->whereKey($this->getData('id'))
            ->lockForUpdate()
            ->first();

        if ($rebateAgreement->settled_at) {
            throw ValidationException::withMessages([
                'id' => [__('The rebate agreement is already settled.')],
            ])->errorBag('settleRebateAgreement');
        }

        $calculation = CalculateRebateAgreement::make(['id' => $rebateAgreement->getKey()])
            ->validate()
            ->execute();

        if (! $calculation->positions) {
            throw ValidationException::withMessages([
                'id' => [__('The volume does not reach any tier of this rebate agreement.')],
            ])->errorBag('settleRebateAgreement');
        }

        if (collect($calculation->positions)->whereNull('vat_rate_id')->isNotEmpty()) {
            throw ValidationException::withMessages([
                'id' => [__('Revenue without a vat rate cannot be settled automatically.')],
            ])->errorBag('settleRebateAgreement');
        }

        $contact = $rebateAgreement->contact;

        $order = CreateOrder::make([
            'tenant_id' => $contact->tenants()->value('tenants.id'),
            'contact_id' => $contact->getKey(),
            'address_invoice_id' => $contact->main_address_id,
            'currency_id' => $calculation->currency_id
                ?? resolve_static(Currency::class, 'default')?->getKey(),
            'order_type_id' => $this->getData('order_type_id'),
        ])
            ->validate()
            ->execute();

        $name = $this->getPositionName($rebateAgreement, $calculation);

        foreach ($calculation->positions as $position) {
            CreateOrderPosition::make([
                'order_id' => $order->getKey(),
                'vat_rate_id' => data_get($position, 'vat_rate_id'),
                'amount' => 1,
                'name' => $name,
                'unit_price' => data_get($position, 'total_net_price'),
                'is_net' => true,
            ])
                ->validate()
                ->execute();
        }

        $order->refresh()->calculatePrices()->save();

        $rebateAgreement->rebate_order_id = $order->getKey();
        $rebateAgreement->settled_at = now();
        $rebateAgreement->save();

        return $order->refresh();
    }

    protected function getPositionName(RebateAgreement $rebateAgreement, Fluent $calculation): string
    {
        return __('Volume rebate :percentage for :start until :end', [
            'percentage' => Number::percentage(bcmul($calculation->percentage, 100), maxPrecision: 2),
            'start' => $rebateAgreement->period_start->locale(app()->getLocale())->isoFormat('L'),
            'end' => $rebateAgreement->period_end->locale(app()->getLocale())->isoFormat('L'),
        ]);
    }

    protected function validateData(): void
    {
        parent::validateData();

        $errors = [];

        $orderTypeEnum = resolve_static(OrderType::class, 'query')
            ->whereKey($this->getData('order_type_id'))
            ->where('is_active', true)
            ->value('order_type_enum');

        if ($orderTypeEnum !== OrderTypeEnum::Refund) {
            $errors['order_type_id'] = [__('The order type must be an active refund order type.')];
        }

        if ($errors) {
            throw ValidationException::withMessages($errors)->errorBag('settleRebateAgreement');
        }
    }
}
