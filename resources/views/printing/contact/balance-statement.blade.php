@php
    $currency = $model->currency?->iso ?? resolve_static(\FluxErp\Models\Currency::class, 'default')->iso;
    $formatter = new NumberFormatter(app()->getLocale(), NumberFormatter::CURRENCY);
@endphp
<x-print.first-page-header :address="$model->invoiceAddress ?? $model->mainAddress">
    <x-slot:right-block>
        <div class="inline-block">
            @section('first-page-right-block')
                <div class="inline-block">
                    @section('first-page-right-block.labels')
                        <div class="font-semibold">
                            {{ __('Customer no.') }}:
                        </div>
                        <div class="font-semibold">
                            {{ __('Date') }}:
                        </div>
                    @show
                </div>
                <div class="pl-6 text-right inline-block">
                    @section('first-page-right-block.values')
                        <div>
                            {{ $model->customer_number }}
                        </div>
                        <div>
                            {{ now()->locale(app()->getLocale())->isoFormat('L') }}
                        </div>
                    @show
                </div>
            @show
        </div>
    </x-slot:right-block>
</x-print.first-page-header>
<main>
    <div class="pt-10 pb-4 prose prose-xs">
        {!! $model->header !!}
    </div>
    <div class="pb-6">
        <table class="w-full table-auto text-xs">
            <thead class="border-b-2 border-black">
            <tr>
                <th class="text-left font-normal">
                    {{ __('Order no.') }}
                </th>
                <th class="text-left font-normal">
                    {{ __('Date') }}
                </th>
                <th class="text-left font-normal">
                    {{ __('Invoice no.') }}
                </th>
                <th class="text-right font-normal uppercase">
                    {{ __('Total Gross') }}
                </th>
                <th class="text-right font-normal uppercase">
                    {{ __('Payments') }}
                </th>
                <th class="text-right font-normal uppercase font-semibold">
                    {{ __('Balance') }}
                </th>
            </tr>
            </thead>
            @foreach ($model->orders()->whereNot('balance', 0)->get(['id', 'order_number', 'invoice_date', 'invoice_number', 'total_gross_price', 'balance']) as $order)
                <tbody class="bg-uneven">
                <tr>
                    <td class="pos py-4 align-top">
                        {{ $order->order_number }}
                    </td>
                    <td class="py-4 align-top">
                        {{ $order->invoice_date->locale(app()->getLocale())->isoFormat('L') }}
                    </td>
                    <td class="pos py-4 align-top">
                        {{ $order->invoice_number }}
                    </td>
                    <td class="py-4 text-right align-top">
                        {{ $formatter->formatCurrency($order->total_gross_price, $currency) }}
                    </td>
                    <td class="py-4 text-right align-top">
                        {{ $formatter->formatCurrency($order->transactions()->sum('amount'), $currency) }}
                    </td>
                    <td class="py-4 text-right align-top">
                        {{ $formatter->formatCurrency($order->balance, $currency) }}
                    </td>
                </tr>
                </tbody>
            @endforeach
        </table>
    </div>
    <div class="pb-6">
        <table class="w-full">
            <tbody class="break-inside-avoid">
                <tr>
                    <td colspan="3" class="border-b border-black font-semibold">
                        {{ __('Total') }}
                    </td>
                    <td class="border-b border-black text-right float-right font-semibold">
                        {{ $formatter->formatCurrency($model->orders()->whereNot('balance', 0)->sum('balance'), $currency) }}
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</main>
