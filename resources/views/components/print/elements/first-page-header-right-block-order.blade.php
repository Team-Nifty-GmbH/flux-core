@props([
    'model',
])

<table
    draggable="false"
    class="w-[6cm]"
>
    <tbody class="align-text-top text-xs leading-none">
    <tr class="leading-none pb-0">
        <td class="text-left font-semibold">
            {{ __('Order no.') }}
        </td>
        <td class="text-left">
            {{ $model->order_number }}
        </td>
    </tr>
    <tr class="leading-none pb-0">
        <td class="text-left font-semibold">
            {{ __('Customer no.') }}
        </td>
        <td class="text-left">
            {{ $model->customer_number }}
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
    @if ($model->commission)
        <tr class="leading-none">
            <td class="py-0 text-left font-semibold">
                {{ __('Commission') }}
            </td>
            <td class="py-0 text-left">
                {{ $model->commission }}
            </td>
        </tr>
    @endif
    </tbody>
</table>
