<x-dynamic-component class="bg-gray-50 py-10 font-light print:p-0" component="{{ $component ?? 'layouts.print' }}">
    @php
        $isNet = $model->is_net;
        $currency = $model->currency->iso;
        $formatter = new NumberFormatter(app()->getLocale(), NumberFormatter::CURRENCY);
    @endphp
    <x-slot name="title">
        {{ __('Invoice') . ' ' . $model->invoice_number }}
    </x-slot>
    <x-slot name="head">
        <style>
            .footer-content {
                position: running(footerRunning);
            }

            .header-content {
                position: running(headerRunning);
            }

            @page {
                size: A4;
                margin: 42mm 20mm 38mm 18mm;
                bleed: 6mm;

                @bottom-center {
                    content: element(footerRunning);
                }

                @top-center {
                    content: element(headerRunning);
                }
            }

            @page:first {
                margin-top:0mm;
                @top-center {
                    content: none;
                }
           }

            .page-count:after {
                counter-increment: page;
                content: "{{ __('Page') }} " counter(page) " {{ __('of') }} " counter(pages);
            }

            @media print {
                body {
                    background: white !important;
                }
            }

            .pagedjs_page_content:has(table) {
                page-break-inside: avoid;
            }

            @if(! $model->invoice_number)
                .pagedjs_page_content {
                    position: relative;
                    margin: 0;
                }

                .pagedjs_page_content:before {
                    content: "";
                    position: absolute;
                    z-index: 9999;
                    top: 0;
                    bottom: 0;
                    left: 0;
                    right: 0;
                    background:
                        url('data:image/svg+xml;utf8,<svg style="transform:rotate(-45deg)" xmlns="http://www.w3.org/2000/svg"  viewBox="0 0 60 70"><text x="0" y="35" fill="%23000" opacity="0.3">{{ __('Preview') }} </text></svg>')
                        0 0/100% 100vh;
                }
            @endif
        </style>
    </x-slot>
    <footer class="hidden">
        <div class="footer-content text-2xs leading-4">
            <div class="absolute left-0 right-0 m-auto max-h-32 w-32 bg-white px-6">
                {{ $model->client->getFirstMedia('logo_small') }}
            </div>
            <div class="w-full pt-5">
                <div class="flex justify-between border-t border-black">
                    <address class="pt-1.5 text-left not-italic">
                        <p class="font-semibold">
                            {{ $model->client->name ?? '' }}
                        </p>
                        <p>
                            {{ $model->client->ceo ?? '' }}
                        </p>
                        <p>
                            {{ $model->client->street ?? '' }}
                        </p>
                        <p>
                            {{ trim(($model->client->zip ?? '') . ' ' . ($model->client->city ?? '')) }}
                        </p>
                        <p>
                            {{ $model->client->phone ?? '' }}
                        </p>
                    </address>
                    <div class="pt-1.5 text-right">
                        <div class="font-semibold">
                            {{ $model->client->bank_name }}
                        </div>
                        <div>
                            {{ $model->client->bank_iban }}
                        </div>
                        <div>
                            {{ $model->client->bank_bic }}
                        </div>
                        <div>
                            <a href="mailto:{{ $model->client->email }}">
                                {{ $model->client->email }}
                            </a>
                        </div>
                        <div>
                            <a href="{{ $model->client->website }}">
                                {{ $model->client->website }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </footer>
    <header class="hidden">
        <div class="header-content">
            <div class="flex items-center justify-between">
                <div class="text-left">
                    <h2 class="text-xl font-semibold uppercase">
                        {{ __('Invoice') . ' ' . $model->invoice_number }}
                    </h2>
                    <div class="page-count text-xs"></div>
                </div>
                <div class="max-h-72 w-44">
                    {{ $model->client->getFirstMedia('logo') }}
                </div>
            </div>
        </div>
    </header>
    <main class="m-auto max-w-7xl rounded-lg bg-white pt-10 text-sm shadow-md print:max-w-full print:rounded-none print:pt-0 print:shadow-none">
        <div class="p-10 print:p-0">
            <div class="grid h-48 content-center">
                <div class="m-auto max-h-72 w-72">
                    {{ $model->client->getFirstMedia('logo') }}
                </div>
            </div>
            <div class="-mt-2 w-full pb-1 text-xs">
                {{ $model->client->name . ' | ' . $model->client->street . ' | ' . $model->client->zip . ' ' . $model->client->city }}
            </div>
            <div class="h-1 w-7 bg-black"></div>
            <div class="flex justify-between">
                <address class="pt-20 not-italic">
                    <div>
                        {{ $model->addressInvoice->company ?? '' }}
                    </div>
                    <div>
                        {{ trim(($model->addressInvoice->firstname ?? '') . ' ' . ($model->addressInvoice->lastname ?? '')) }}
                    </div>
                    <div>
                        {{ $model->addressInvoice->street ?? '' }}
                    </div>
                    <div>
                        {{ trim(($model->addressInvoice->zip ?? '') . ' ' . ($model->addressInvoice->city ?? '')) }}
                    </div>
                </address>
                <div class="flex items-end">
                    <div>
                        <div class="flex">
                            <div>
                                <div class="font-semibold">
                                    {{ __('Order no.') }}:
                                </div>
                                <div class="font-semibold">
                                    {{ __('Date') }}:
                                </div>
                                <div class="font-semibold">
                                    {{ __('Customer no.') }}:
                                </div>
                            </div>
                            <div class="pl-6 text-right">
                                <div>
                                    {{ $model->order_number }}
                                </div>
                                <div>
                                    {{ $model->order_date
                                                ->locale(app()->getLocale())
                                                ->isoFormat('L') }}
                                </div>
                                <div>
                                    {{ $model->addressInvoice->contact->customer_number }}
                                </div>
                            </div>
                        </div>
                    </div>
                    </div>
            </div>
            <h1 class="pt-32 text-2xl font-semibold uppercase">
                {{ __('Invoice') . ' ' . $model->invoice_number ?: __('Preview') }}
            </h1>
            <div class="pt-10 pb-16 text-sm">
                {{ $model->header }}
            </div>
            <div class="pb-6">
                <table class="w-full table-auto text-sm">
                    <thead class="border-b-2 border-black">
                    <tr>
                        <th class="pr-8 text-left font-normal">
                            {{ __('pos') }}
                        </th>
                        <th class="pr-8 text-left font-normal">
                            {{ __('name') }}
                        </th>
                        <th class="pr-8 text-center font-normal">
                            {{ __('amount') }}
                        </th>
                        <th class="text-right font-normal uppercase">
                            {{ __('sum') }}
                        </th>
                    </tr>
                    </thead>
                    @foreach ($model->orderPositions as $position)
                        <tbody class="break-inside-avoid odd:bg-neutral-50">
                        <tr>
                            <td class="pos py-4 pr-8 align-top">
                                {{ $position->total_net_price ? $position->slug_position : '' }}
                            </td>
                            <td class="py-4 pr-8 align-top" style="padding-left: {{ $position->depth * 15 }}px">
                                <p class="font-semibold">
                                    {{ $position->name }}
                                </p>
                                <p>
                                    {!! $position->description !!}
                                </p>
                            </td>
                            <td class="py-4 pr-8 text-center align-top">
                                {{ format_number($position->amount) }}
                            </td>
                            <td class="py-4 text-right align-top">
                                <div class="line-through">
                                    {{ $position->total_base_net_price > $position->total_net_price ? $formatter->formatCurrency($position->total_base_net_price, $currency) : '' }}
                                </div>
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
                            <td colspan="4">
                                <div>
                                    <div class="border-b-2 border-black font-semibold uppercase">
                                        {{ __('Summary') }}
                                    </div>
                                    <div class="text-sm">
                                        @foreach($summary as $summaryItem)
                                            <div class="flex justify-between py-2.5">
                                                <div class="flex">
                                                    <div class="pr-8">
                                                        {{ $summaryItem->slug_position }}
                                                    </div>
                                                    <div>
                                                        {{ $summaryItem->name }}
                                                    </div>
                                                </div>
                                                <div>
                                                    {{ $formatter->formatCurrency($summaryItem->total_net_price, $currency) }}
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            @endif
            <div class="w-full pb-16">
                <table class="w-full">
                    <tbody class="break-inside-avoid">
                    <tr>
                        <td colspan="4">
                            <div>
                                <div class="border-b-2 border-black font-semibold uppercase">
                                    {{ __('total') }}
                                </div>
                                <div class="text-sm">
                                    <div class="flex justify-between py-2.5">
                                        <div>
                                            {{ __('Sum net') }}
                                        </div>
                                        <div>
                                            {{ $formatter->formatCurrency($model->total_net_price, $currency) }}
                                        </div>
                                    </div>
                                    @foreach($model->total_vats as $vat)
                                        <div class="flex justify-between py-2.5">
                                            <div>
                                                {{ __('Plus ') }} {{ format_number($vat['vat_rate_percentage'], NumberFormatter::PERCENT) }}
                                            </div>
                                            <div>
                                                {{ $formatter->formatCurrency($vat['total_vat_price'], $currency) }}
                                            </div>
                                        </div>
                                    @endforeach
                                    <div class="flex justify-between bg-gray-50 py-2.5 font-bold">
                                        <div>
                                            {{ __('Total gross') }}
                                        </div>
                                        <div>
                                            {{ $formatter->formatCurrency($model->total_gross_price, $currency) }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
            <div class="break-inside-avoid">
                {{ $model->footer }}
            </div>
        </div>
    </main>
</x-dynamic-component>
