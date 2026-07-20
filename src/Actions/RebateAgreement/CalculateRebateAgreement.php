<?php

namespace FluxErp\Actions\RebateAgreement;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Order;
use FluxErp\Models\OrderPosition;
use FluxErp\Models\RebateAgreement;
use FluxErp\Rulesets\RebateAgreement\CalculateRebateAgreementRuleset;
use FluxErp\States\Order\Canceled;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Fluent;
use Illuminate\Validation\ValidationException;

class CalculateRebateAgreement extends FluxAction
{
    public static function models(): array
    {
        return [RebateAgreement::class];
    }

    protected function getRulesets(): string|array
    {
        return CalculateRebateAgreementRuleset::class;
    }

    public function performAction(): Fluent
    {
        $rebateAgreement = resolve_static(RebateAgreement::class, 'query')
            ->whereKey($this->getData('id'))
            ->first();

        $currencyIds = $this->getOrdersQuery($rebateAgreement)
            ->distinct()
            ->pluck('currency_id');

        if ($currencyIds->count() > 1) {
            throw ValidationException::withMessages([
                'id' => [__('The orders within the period use different currencies.')],
            ])->errorBag('calculateRebateAgreement');
        }

        $volumeByVatRate = $this->getVolumeByVatRate($rebateAgreement);
        $volume = bcround(
            $volumeByVatRate->reduce(
                fn (string $carry, OrderPosition $item) => bcadd($carry, $item->total_net_price ?? 0, 9),
                '0'
            ),
            2
        );

        $percentage = $rebateAgreement->resolvePercentage($volume);

        if (is_null($percentage)) {
            return new Fluent([
                'rebate_agreement_id' => $rebateAgreement->getKey(),
                'currency_id' => $currencyIds->first(),
                'volume' => $volume,
                'percentage' => null,
                'total_net_price' => '0.00',
                'positions' => [],
            ]);
        }

        $totalNetPrice = bcround(bcmul($volume, $percentage, 9), 2);

        return new Fluent([
            'rebate_agreement_id' => $rebateAgreement->getKey(),
            'currency_id' => $currencyIds->first(),
            'volume' => $volume,
            'percentage' => $percentage,
            'total_net_price' => $totalNetPrice,
            'positions' => $this->splitByVatRate($volumeByVatRate, $totalNetPrice),
        ]);
    }

    protected function getOrdersQuery(RebateAgreement $rebateAgreement): Builder
    {
        return resolve_static(Order::class, 'query')
            ->revenue()
            ->where('contact_id', $rebateAgreement->contact_id)
            ->whereNotNull('invoice_number')
            ->whereBetween(
                'invoice_date',
                [$rebateAgreement->period_start, $rebateAgreement->period_end]
            )
            ->whereNotState('state', Canceled::class)
            ->whereDoesntHave('rebateAgreement');
    }

    protected function getVolumeByVatRate(RebateAgreement $rebateAgreement): Collection
    {
        return resolve_static(OrderPosition::class, 'query')
            ->whereIn('order_id', $this->getOrdersQuery($rebateAgreement)->select('orders.id'))
            ->where('is_alternative', false)
            ->where(
                fn (Builder $query) => $query
                    ->where('is_free_text', false)
                    ->orWhereDoesntHave('children')
            )
            ->groupBy(['vat_rate_percentage', 'vat_rate_id'])
            ->selectRaw('vat_rate_percentage, vat_rate_id, SUM(total_net_price) AS total_net_price')
            ->get();
    }

    protected function splitByVatRate(Collection $volumeByVatRate, string $totalNetPrice): array
    {
        $contributing = $volumeByVatRate->filter(
            fn (OrderPosition $item) => bccomp($item->total_net_price ?? 0, 0, 9) === 1
        );

        $base = $contributing->reduce(
            fn (string $carry, OrderPosition $item) => bcadd($carry, $item->total_net_price, 9),
            '0'
        );

        if (bccomp($base, 0, 9) !== 1) {
            return [];
        }

        $totalCents = bcmul($totalNetPrice, 100, 0);
        $positions = $contributing
            ->map(function (OrderPosition $item) use ($base, $totalCents): array {
                $exactCents = bcdiv(bcmul($totalCents, $item->total_net_price, 9), $base, 9);
                $cents = bcfloor($exactCents);

                return [
                    'vat_rate_id' => $item->vat_rate_id,
                    'vat_rate_percentage' => $item->vat_rate_percentage,
                    'cents' => $cents,
                    'remainder' => bcsub($exactCents, $cents, 9),
                ];
            })
            ->sortByDesc('remainder')
            ->values()
            ->all();

        $remainingCents = bcsub(
            $totalCents,
            array_reduce($positions, fn (string $carry, array $item) => bcadd($carry, $item['cents'], 0), '0'),
            0
        );

        foreach ($positions as $index => $position) {
            if (bccomp($remainingCents, 0, 0) !== 1) {
                break;
            }

            $positions[$index]['cents'] = bcadd($position['cents'], 1, 0);
            $remainingCents = bcsub($remainingCents, 1, 0);
        }

        return collect($positions)
            ->sortByDesc(fn (array $position) => (float) $position['vat_rate_percentage'])
            ->map(fn (array $position) => [
                'vat_rate_id' => $position['vat_rate_id'],
                'vat_rate_percentage' => $position['vat_rate_percentage'],
                'total_net_price' => bcdiv($position['cents'], 100, 2),
            ])
            ->values()
            ->all();
    }
}
