<?php

namespace FluxErp\Traits\Livewire;

use Livewire\Attributes\Js;

trait HasTemporalXAxisFormatter
{
    #[Js]
    public function xAxisFormatter(): string
    {
        return <<<'JS'
            let name;
            if (typeof val === 'string' && val.includes('->')) {
                name = val.split('->')[1];
                val = val.split('->')[0];
            }

            if (/^\d{4}$/.test(val)) {
                return val + (name ? ' (' + name + ')' : '');
            }

            if (/^\d{4}-\d{2}$/.test(val)) {
                const [year, month] = val.split('-');
                const date = new Date(year, month - 1);
                return date.toLocaleDateString(document.documentElement.lang, { year: 'numeric', month: 'long' })
                       + (name ? ' (' + name + ')' : '');
            }

            const date = new Date(val);
            if (!isNaN(date.getTime())) {
                return date.toLocaleDateString(document.documentElement.lang) + (name ? ' (' + name + ')' : '');
            }

            return val + (name ? ' (' + name + ')' : '');
        JS;
    }
}
