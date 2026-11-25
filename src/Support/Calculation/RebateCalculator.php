<?php

namespace FluxErp\Support\Calculation;

use FluxErp\Models\Order;
use FluxErp\Models\OrderPosition;
use FluxErp\Models\RebateAgreement;
use FluxErp\States\Order\Canceled;
use FluxErp\Traits\Makeable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class RebateCalculator
{
    use Makeable;

    public function __construct(protected RebateAgreement $rebateAgreement) {}

    public function calculate(): array
    {
        $volumeByVatRate = $this->getVolumeByVatRate();
        $volume = bcround(
            $volumeByVatRate->reduce(
                fn (string $carry, OrderPosition $item) => bcadd($carry, $item->total_net_price ?? 0, 9),
                '0'
            ),
            2
        );

        $percentage = $this->rebateAgreement->resolvePercentage($volume);

        if (is_null($percentage)) {
            return [
                'rebate_agreement_id' => $this->rebateAgreement->getKey(),
                'volume' => $volume,
                'percentage' => null,
                'total_net_price' => '0.00',
                'positions' => [],
            ];
        }

        $totalNetPrice = bcround(bcmul($volume, $percentage, 9), 2);

        return [
            'rebate_agreement_id' => $this->rebateAgreement->getKey(),
            'volume' => $volume,
            'percentage' => $percentage,
            'total_net_price' => $totalNetPrice,
            'positions' => $this->splitByVatRate($volumeByVatRate, $totalNetPrice),
        ];
    }

    public function getCurrencyIds(): Collection
    {
        return $this->getOrdersQuery()
            ->distinct()
            ->pluck('currency_id');
    }

    protected function getOrdersQuery(): Builder
    {
        return resolve_static(Order::class, 'query')
            ->revenue()
            ->where('contact_id', $this->rebateAgreement->contact_id)
            ->whereNotNull('invoice_number')
            ->whereBetween(
                'invoice_date',
                [$this->rebateAgreement->period_start, $this->rebateAgreement->period_end]
            )
            ->whereNotState('state', Canceled::class)
            ->whereDoesntHave('rebateAgreement');
    }

    protected function getVolumeByVatRate(): Collection
    {
        return resolve_static(OrderPosition::class, 'query')
            ->whereIn('order_id', $this->getOrdersQuery()->select('orders.id'))
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
