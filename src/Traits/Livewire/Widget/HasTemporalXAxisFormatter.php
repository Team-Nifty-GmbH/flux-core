<?php

namespace FluxErp\Traits\Livewire\Widget;

use Livewire\Attributes\Js;

trait HasTemporalXAxisFormatter
{
    #[Js]
    public function xAxisFormatter(): string
    {
        $weekLabel = __('CW');

        return <<<JS
            let name;
            if (typeof val === 'string' && val.includes('->')) {
                name = val.split('->')[1];
                val = val.split('->')[0];
            }

            if (/^\d{4}\$/.test(val)) {
                return "'" + val.slice(-2) + (name ? ' (' + name + ')' : '');
            }

            if (/^\d{4}-W\d{2}\$/.test(val)) {
                const [year, week] = val.split('-W');
                return '{$weekLabel} ' + parseInt(week, 10) + " '" + year.slice(-2) + (name ? ' (' + name + ')' : '');
            }

            if (/^\d{4}-\d{2}\$/.test(val)) {
                const [year, month] = val.split('-');
                const date = new Date(year, parseInt(month, 10) - 1);
                return date.toLocaleDateString(document.documentElement.lang, { year: '2-digit', month: 'short' })
                       + (name ? ' (' + name + ')' : '');
            }

            const date = new Date(val);
            if (!isNaN(date.getTime())) {
                return date.toLocaleDateString(document.documentElement.lang, { year: '2-digit', month: '2-digit', day: '2-digit' })
                       + (name ? ' (' + name + ')' : '');
            }

            return val + (name ? ' (' + name + ')' : '');
        JS;
    }
}
