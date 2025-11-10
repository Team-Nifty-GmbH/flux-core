@props([
    'model',
])

<table class="w-[6cm]">
    <tbody class="w-full align-text-top text-xs leading-none">
        <tr class="leading-none">
            <td class="text-left font-semibold">
                {{ __('Order no.') }}
            </td>
            <td class="text-left">
                {{ data_get($model, 'order_number', '') }}
            </td>
        </tr>
        <tr class="leading-none">
            <td class="text-left font-semibold">
                {{ __('Customer no.') }}
            </td>
            <td class="text-left">
                {{ data_get($model, 'customer_number', '') }}
            </td>
        </tr>
        <tr class="leading-none">
            <td class="text-left font-semibold">
                {{ __('Order Date') }}
            </td>
            <td class="text-left">
                {{ $model->order_date->locale(app()->getLocale())->isoFormat('L') }}
            </td>
        </tr>
        @if (data_get($model, 'commission'))
            <tr class="leading-none">
                <td class="py-0 text-left font-semibold">
                    {{ __('Commission') }}
                </td>
                <td class="py-0 text-left">
                    {{ data_get($model, 'commission', '') }}
                </td>
            </tr>
        @endif

        <tr>
            <td class="py-0 text-left font-semibold">
                {{ __('Invoice Date') }}:
            </td>
            <td class="p-0 text-left">
                {{ ($model->invoice_date ?: now())->locale(app()->getLocale())->isoFormat('L') }}
            </td>
        </tr>
        <tr>
            <td class="py-0 text-left font-semibold">
                {{ __('Performance Date') }}:
            </td>
            <td class="p-0 text-left">
                @if ($model->system_delivery_date_end && $model->system_delivery_date_end->format('Y-m-d') !== $model->system_delivery_date->format('Y-m-d'))
                    {{ ($model->system_delivery_date ?: now())->locale(app()->getLocale())->isoFormat('L') }}
                    -
                    {{ ($model->system_delivery_date_end ?: now())->locale(app()->getLocale())->isoFormat('L') }}
                @else
                    {{ ($model->system_delivery_date ?: now())->locale(app()->getLocale())->isoFormat('L') }}
                @endif
            </td>
        </tr>
    </tbody>
</table>
