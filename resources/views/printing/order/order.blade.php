<x-layouts.print>
    @php
        $isNet = $model->priceList->is_net;
        $currency = $model->currency->iso;
        $formatter = new NumberFormatter(app()->getLocale(), NumberFormatter::CURRENCY);
    @endphp
    <x-print.first-page-header :address="$model->addressInvoice">
        <x-slot:right-block>
                <div class="inline-block">
                @section('first-page-right-block')
                    <div class="inline-block">
                        @section('first-page-right-block.labels')
                            <div class="font-semibold">
                                {{ __('Order no.') }}:
                            </div>
                            <div class="font-semibold">
                                {{ __('Customer no.') }}:
                            </div>
                            <div class="font-semibold">
                                {{ __('Order Date') }}:
                            </div>
                        @show
                    </div>
                    <div class="pl-6 text-right inline-block">
                        @section('first-page-right-block.values')
                        <div>
                            {{ $model->order_number }}
                        </div>
                        <div>
                            {{ $model->addressInvoice->contact->customer_number }}
                        </div>
                        <div>
                            {{ $model->order_date->locale(app()->getLocale())->isoFormat('L') }}
                        </div>
                        @show
                    </div>
                @show
                </div>
        </x-slot:right-block>
    </x-print.first-page-header>
    <main>
        <div class="pt-10 pb-4">
            {!! $model->header !!}
        </div>
        <div class="pb-6">
            <table class="w-full table-auto text-xs">
                <thead class="border-b-2 border-black">
                <tr>
                    <th class="pr-8 text-left font-normal">
                        {{ __('Pos.') }}
                    </th>
                    <th class="pr-8 text-left font-normal">
                        {{ __('Name') }}
                    </th>
                    <th class="pr-8 text-center font-normal">
                        {{ __('Amount') }}
                    </th>
                    <th class="text-right font-normal uppercase">
                        {{ __('Sum') }}
                    </th>
                </tr>
                </thead>
                @foreach ($model->orderPositions as $position)
                    <tbody class="bg-uneven">
                    <tr>
                        <td class="pos py-4 pr-8 align-top">
                            {{ $position->total_net_price ? $position->slug_position : '' }}
                        </td>
                        <td class="py-4 pr-8 align-top" style="padding-left: {{ $position->depth * 15 }}px">
                            <p class="font-italic text-xs">
                                {{ $position->product_number }}
                            </p>
                            <p class="font-semibold">
                                {{ $position->name }}
                            </p>
                            <p class="prose prose-sm">
                                {!! $position->description !!}
                            </p>
                        </td>
                        <td class="py-4 pr-8 text-center align-top">
                            {{ format_number($position->amount) }}
                        </td>
                        <td class="py-4 text-right align-top">
                            @if($position->total_base_net_price > $position->total_net_price)
                                <div class="text-xs whitespace-nowrap">
                                    <div class="line-through">
                                        {{ $formatter->formatCurrency($isNet ? $position->total_net_price : $position->total_gross_price, $currency) }}
                                    </div>
                                    <div>
                                        -{{ format_number(diff_percentage($position->total_base_net_price, $position->total_net_price), NumberFormatter::PERCENT) }}
                                    </div>
                                </div>
                            @endif
                            {{ $position->total_net_price ? $formatter->formatCurrency($isNet ? $position->total_net_price : $position->total_gross_price, $currency) : null }}
                        </td>
                    </tr>
                    </tbody>
                @endforeach
            </table>
        </div>
        @if($summary)
            <div class="pb-6">
                <table class="w-full">
                    <tbody class="break-inside-avoid">
                    <tr>
                        <td colspan="3" class="border-b border-black font-semibold">
                            {{ __('Summary') }}
                        </td>
                    </tr>
                    @foreach($summary as $summaryItem)
                        <tr>
                            <td>
                                {{ $summaryItem->slug_position }}
                            </td>
                            <td class="whitespace-nowrap">
                                {{ $summaryItem->name }}
                            </td>
                            <td class="text-right float-right">
                                {{ $formatter->formatCurrency($summaryItem->total_net_price, $currency) }}
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @endif
        @section('total')
            <table class="w-full pb-16 text-xs">
                <tbody class="break-inside-avoid">
                    <tr>
                        <td colspan="2" class="border-b-2 border-black font-semibold">
                            {{ __('Total') }}
                        </td>
                    </tr>
                    <tr>
                        <td class="text-right">
                            {{ __('Sum net') }}
                        </td>
                        <td class="text-right w-0 whitespace-nowrap pl-12">
                            {{ $formatter->formatCurrency($model->total_net_price, $currency) }}
                        </td>
                    </tr>
                    @foreach($model->total_vats ?? [] as $vat)
                        <tr>
                            <td class="text-right">
                                {{ __('Plus ') }} {{ format_number($vat['vat_rate_percentage'], NumberFormatter::PERCENT) }}
                            </td>
                            <td class="text-right w-0 whitespace-nowrap pl-12">
                                {{ $formatter->formatCurrency($vat['total_vat_price'], $currency) }}
                            </td>
                        </tr>
                    @endforeach
                    <tr class="font-bold">
                        <td class="text-right">
                            {{ __('Total Gross') }}
                        </td>
                        <td class="text-right w-0 whitespace-nowrap pl-12">
                            {{ $formatter->formatCurrency($model->total_gross_price, $currency) }}
                        </td>
                    </tr>
                </tbody>
            </table>
        @show
        @section('footer')
            <div class="break-inside-avoid">
                {{ $model->footer }}
            </div>
        @show
    </main>
</x-layouts.print>
