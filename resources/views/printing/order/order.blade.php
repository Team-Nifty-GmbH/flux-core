@php
    $isNet = $model->priceList->is_net;
    $currency = $model->currency->iso;
    $formatter = new NumberFormatter(app()->getLocale(), NumberFormatter::CURRENCY);
@endphp

@section('first-page-header')
<x-flux::print.first-page-header :address="$model->addressInvoice" :$model>
    <x-slot:right-block>
        @section('first-page-right-block')
        <table class="border-separate border-spacing-x-2">
            <tbody class="align-text-top text-xs leading-none">
                @section('first-page-right-block.rows')
                <tr class="leading-none">
                    <td class="py-0 text-right font-semibold">
                        {{ __('Order no.') }}
                    </td>
                    <td class="py-0">
                        {{ $model->order_number }}
                    </td>
                </tr>
                <tr class="leading-none">
                    <td class="py-0 text-right font-semibold">
                        {{ __('Customer no.') }}
                    </td>
                    <td class="py-0">
                        {{ $model->addressInvoice->contact->customer_number }}
                    </td>
                </tr>
                <tr class="leading-none">
                    <td class="py-0 text-right font-semibold">
                        {{ __('Order Date') }}
                    </td>
                    <td class="py-0">
                        {{ $model->order_date->locale(app()->getLocale())->isoFormat('L') }}
                    </td>
                </tr>
                @if ($model->commission)
                    <tr class="leading-none">
                        <td class="py-0 text-right font-semibold">
                            {{ __('Commission') }}
                        </td>
                        <td class="py-0">
                            {{ $model->commission }}
                        </td>
                    </tr>
                @endif

                @show
            </tbody>
        </table>
        @show
    </x-slot>
</x-flux::print.first-page-header>
@show
<main>
    @section('header')
    <div class="prose-xs prose pb-4 pt-10">
        {!! Blade::render(html_entity_decode($model->header ?? ''), ['model' => $model]) !!}
    </div>
    @show
    <div class="pb-6">
        @section('positions')
        <table class="w-full table-auto text-xs">
            <thead class="border-b-2 border-black">
                @section('positions.header')
                <tr class="py-2">
                    <th class="py-2 pr-8 text-left font-normal">
                        {{ __('Pos.') }}
                    </th>
                    <th class="py-2 pr-8 text-left font-normal">
                        {{ __('Name') }}
                    </th>
                    <th class="py-2 pr-8 text-center font-normal">
                        {{ __('Amount') }}
                    </th>
                    <th class="py-2 text-right font-normal uppercase">
                        {{ __('Sum') }}
                    </th>
                </tr>
                @show
            </thead>
            @section('positions.positions')
            @foreach ($model->orderPositions as $position)
                <x-flux::print.order.order-position
                    :position="$position"
                    :is-net="$isNet"
                    :currency="$currency"
                    :formatter="$formatter"
                />
            @endforeach

            @show
        </table>
        @show
    </div>
    @if ($summary)
        @section('summary')
        <div class="pb-6">
            <table class="w-full text-xs">
                <tbody class="break-inside-avoid">
                    <tr>
                        <td
                            colspan="3"
                            class="border-b border-black font-semibold"
                        >
                            {{ __('Summary') }}
                        </td>
                    </tr>
                    @foreach ($summary as $summaryItem)
                        <tr>
                            <td>
                                {{ $summaryItem->slug_position }}
                            </td>
                            <td class="whitespace-nowrap">
                                {{ $summaryItem->name }}
                            </td>
                            <td class="float-right text-right">
                                {{ $formatter->formatCurrency($summaryItem->total_net_price, $currency) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @show
    @endif

    @section('total')
    <table class="w-full pb-16 text-xs">
        <tbody class="break-inside-avoid">
            <tr>
                <td colspan="2" class="border-b-2 border-black font-semibold">
                    {{ __('Total') }}
                </td>
            </tr>
            @section('total.discounts')
            @if ($model->discounts->isNotEmpty())
                <tr>
                    <td class="text-right">
                        {{ __('Sum net without discount') }}
                    </td>
                    <td class="w-0 whitespace-nowrap pl-12 text-right">
                        {{ $formatter->formatCurrency($model->total_base_net_price, $currency) }}
                    </td>
                </tr>
                @foreach ($model->discounts as $discount)
                    <tr>
                        <td class="text-right">
                            <span>{{ data_get($discount, 'name') }}</span>
                            <span>
                                {{ \Illuminate\Support\Number::percentage(bcmul(data_get($discount, 'discount_percentage', 0), 100)) }}
                            </span>
                        </td>
                        <td class="w-0 whitespace-nowrap pl-12 text-right">
                            {{ $formatter->formatCurrency(bcmul(data_get($discount, 'discount_flat', 0), -1), $currency) }}
                        </td>
                    </tr>
                @endforeach

                <tr class="border-b"></tr>
            @endif

            @show
            @section('total.net')
            <tr>
                <td class="text-right">
                    {{ __('Sum net') }}
                </td>
                <td class="w-0 whitespace-nowrap pl-12 text-right">
                    {{ $formatter->formatCurrency($model->total_net_price, $currency) }}
                </td>
            </tr>
            @show
            @section('total.vats')
            @foreach ($model->total_vats ?? [] as $vat)
                <tr>
                    <td class="text-right">
                        {{
                            __('Plus :percentage VAT from :total_net', [
                                'percentage' => format_number($vat['vat_rate_percentage'], NumberFormatter::PERCENT),
                                'total_net' => $formatter->formatCurrency($vat['total_net_price'], $currency),
                            ])
                        }}
                    </td>
                    <td class="w-0 whitespace-nowrap pl-12 text-right">
                        {{ $formatter->formatCurrency($vat['total_vat_price'], $currency) }}
                    </td>
                </tr>
            @endforeach

            @show
            @section('total.gross')
            <tr class="font-bold">
                <td class="text-right">
                    {{ __('Total Gross') }}
                </td>
                <td class="w-0 whitespace-nowrap pl-12 text-right">
                    {{ $formatter->formatCurrency($model->total_gross_price, $currency) }}
                </td>
            </tr>
            @show
        </tbody>
    </table>
    @show
    @section('footer')
    <div class="prose-xs prose break-inside-avoid">
        {!! Blade::render(html_entity_decode($model->footer ?? ''), ['model' => $model]) !!}
        {!!
            $model
                ->vatRates()
                ->distinct()
                ->pluck('footer_text')
                ->filter()
                ->map(fn (string $text) => Blade::render(html_entity_decode($text), ['model' => $model]))
                ->implode('<br>')
        !!}
    </div>
    @show
</main>
