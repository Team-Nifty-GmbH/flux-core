@props([
    'model',
])

<table draggable="false" class="w-[6cm]">
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
    </tbody>
</table>
