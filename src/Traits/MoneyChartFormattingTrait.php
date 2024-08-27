<?php

namespace FluxErp\Traits;

use Livewire\Attributes\Js;

trait MoneyChartFormattingTrait
{
    #[Js]
    public function toolTipFormatter(): string
    {
        return $this->moneyFormatterJs();
    }

    #[Js]
    public function dataLabelsFormatter(): string
    {
        return $this->moneyFormatterJs();
    }

    #[Js]
    public function yAxisFormatter(): string
    {
        return $this->moneyFormatterJs();
    }

    protected function moneyFormatterJs(): string
    {
        return <<<'JS'
            return window.formatters.money(val);
        JS;
    }
}
