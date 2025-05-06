@use(\FluxErp\Models\Currency)
@php
    $currency = ($model->currency ?? Currency::default())->iso;
    $formatter = new NumberFormatter(app()->getLocale(), NumberFormatter::CURRENCY);
@endphp

<x-flux::print.first-page-header
    :address="$model->invoiceAddress ?? $model->mainAddress"
>
    <x-slot:right-block>
        <div class="inline-block">
            @section('first-page-right-block')
            <div class="inline-block">
                @section('first-page-right-block.labels')
                <div class="font-semibold">{{ __('Customer no.') }}:</div>
                <div class="font-semibold">{{ __('Date') }}:</div>
                @show
            </div>
            <div class="inline-block pl-6 text-right">
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
    </x-slot>
</x-flux::print.first-page-header>
<main>
    <div class="prose-xs prose pb-4 pt-10">
        {!! $model->header !!}
    </div>
    <div class="pb-6">
        @section('positions')
        <table class="w-full table-auto text-xs">
            <thead class="border-b-2 border-black">
                @section('positions.header')
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
                    <th class="text-right font-semibold uppercase">
                        {{ __('Balance') }}
                    </th>
                </tr>
                @show
            </thead>
            @section('positions.body')
            @foreach ($model->orders()->unpaid()->get(['id', 'order_number', 'invoice_date', 'invoice_number', 'total_gross_price', 'balance']) as $order)
                <x-flux::print.order.order
                    :order="$order"
                    :currency="$currency"
                    :formatter="$formatter"
                />
            @endforeach

            @show
        </table>
        @show
    </div>
    <div class="pb-6">
        @section('total')
        <table class="w-full">
            <tbody class="break-inside-avoid">
                <tr>
                    <td colspan="3" class="border-b border-black font-semibold">
                        {{ __('Total') }}
                    </td>
                    <td
                        class="float-right border-b border-black text-right font-semibold"
                    >
                        {{ $formatter->formatCurrency($model->orders()->unpaid()->sum('balance'),$currency,) }}
                    </td>
                </tr>
            </tbody>
        </table>
        @show
    </div>
</main>
