<?php

namespace FluxErp\View\Printing\Order;

use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;

class FinalInvoice extends Invoice
{
    public function render(): View|Factory
    {
        return view('flux::printing.order.final-invoice', [
            'model' => $this->model,
            'summary' => $this->summary,
        ]);
    }

    public function getSubject(): string
    {
        return __('Final Invoice') . ' ' . ($this->model->invoice_number ?: __('Preview'));
    }

    public function prepareModel(): void
    {
        parent::prepareModel();

        $this->calculateTotals();
    }

    protected function getVatKey(array $vat): int|string
    {
        return data_get($vat, 'vat_rate_id') ?? data_get($vat, 'vat_rate_percentage');
    }

    protected function calculateTotals(): void
    {
        if (! is_null($this->model->subtotal_net_price)
            && ! is_null($this->model->subtotal_gross_price)
        ) {
            return;
        }

        // Calculate totals for the final invoice
        $this->model->subtotal_net_price = $totalNetPrice = $this->model->total_net_price;
        $this->model->subtotal_gross_price = $this->model->total_gross_price;
        $this->model->subtotal_vats = $this->model->total_vats;

        $totalVats = collect($this->model->total_vats ?? [])
            ->keyBy(fn (array $vat): int|string => $this->getVatKey($vat))
            ->all();

        $this->model->setRelation('children', $this->model->getAllDescendantsQuery()->get());

        foreach ($this->model->children as $child) {
            $totalNetPrice = bcsub($totalNetPrice, $child->total_net_price);
            foreach ($child->total_vats ?? [] as $childVat) {
                $vatRatePercentage = data_get($childVat, 'vat_rate_percentage');

                $childVatKey = $this->getVatKey($childVat);

                if (is_null(data_get($totalVats, $childVatKey))) {
                    $totalVats[$childVatKey] = [
                        'vat_rate_id' => data_get($childVat, 'vat_rate_id'),
                        'vat_rate_percentage' => $vatRatePercentage,
                        'total_vat_price' => 0,
                        'total_net_price' => 0,
                    ];
                }

                data_set(
                    $totalVats[$childVatKey],
                    'total_vat_price',
                    bcsub(
                        data_get($totalVats[$childVatKey], 'total_vat_price') ?? 0,
                        data_get($childVat, 'total_vat_price') ?? 0,
                    )
                );
                data_set(
                    $totalVats[$childVatKey],
                    'total_net_price',
                    bcsub(
                        data_get($totalVats[$childVatKey], 'total_net_price') ?? 0,
                        data_get($childVat, 'total_net_price') ?? 0,
                    )
                );
            }

            $this->model->margin = bcsub(
                $this->model->margin,
                $child->margin
            );
            $this->model->gross_profit = bcsub(
                $this->model->gross_profit,
                $child->gross_profit
            );
        }

        $totalGrossPrice = bcadd(
            $totalNetPrice,
            array_reduce(
                $totalVats,
                function ($carry, array $vat): string {
                    return bcadd($carry, data_get($vat, 'total_vat_price') ?? 0);
                },
                0
            )
        );

        $this->model->total_net_price = $totalNetPrice;
        $this->model->total_gross_price = $totalGrossPrice;
        $this->model->total_vats = array_values(
            array_filter(
                $totalVats,
                fn (array $vat): bool => bccomp(data_get($vat, 'total_net_price') ?? 0, 0) !== 0
            )
        );
    }
}
