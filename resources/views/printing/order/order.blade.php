@use(\Illuminate\Support\Number)
@use(\FluxErp\Models\PriceList)
@use(\FluxErp\Models\Currency)
@use(\Illuminate\Support\Fluent)
@php
    $isNet = ($model->priceList ?? resolve_static(PriceList::class, 'default'))->is_net;
@endphp

@section('first-page-header')
    <x-flux::print.first-page-header
        :address="Fluent::make($model->address_invoice)"
        :$model
    >
        <x-slot:right-block>
            @section('first-page-right-block')
                <table style="border-collapse: separate; border-spacing: 8px 0">
                    <tbody
                        style="
                            vertical-align: text-top;
                            font-size: 12px;
                            line-height: 1;
                        "
                    >
                        @section('first-page-right-block.rows')
                            <tr style="line-height: 1">
                                <td
                                    style="
                                        padding-top: 0;
                                        padding-bottom: 0;
                                        text-align: left;
                                        font-weight: 600;
                                    "
                                >
                                    {{ __('Order no.') }}
                                </td>
                                <td
                                    style="
                                        padding-top: 0;
                                        padding-bottom: 0;
                                        text-align: right;
                                    "
                                >
                                    {{ $model->order_number }}
                                </td>
                            </tr>
                            <tr style="line-height: 1">
                                <td
                                    style="
                                        padding-top: 0;
                                        padding-bottom: 0;
                                        text-align: left;
                                        font-weight: 600;
                                    "
                                >
                                    {{ __('Customer no.') }}
                                </td>
                                <td
                                    style="
                                        padding-top: 0;
                                        padding-bottom: 0;
                                        text-align: right;
                                    "
                                >
                                    {{ $model->contact()->withTrashed()->value('customer_number') }}
                                </td>
                            </tr>
                            <tr style="line-height: 1">
                                <td
                                    style="
                                        padding-top: 0;
                                        padding-bottom: 0;
                                        text-align: left;
                                        font-weight: 600;
                                    "
                                >
                                    {{ __('Order Date') }}
                                </td>
                                <td
                                    style="
                                        padding-top: 0;
                                        padding-bottom: 0;
                                        text-align: right;
                                    "
                                >
                                    {{ $model->order_date->locale(app()->getLocale())->isoFormat('L') }}
                                </td>
                            </tr>
                            @if($model->commission)
                                <tr style="line-height: 1">
                                    <td
                                        style="
                                            padding-top: 0;
                                            padding-bottom: 0;
                                            text-align: left;
                                            font-weight: 600;
                                        "
                                    >
                                        {{ __('Commission') }}
                                    </td>
                                    <td
                                        style="
                                            padding-top: 0;
                                            padding-bottom: 0;
                                            text-align: right;
                                        "
                                    >
                                        {{ $model->commission }}
                                    </td>
                                </tr>
                            @endif

                        @show
                    </tbody>
                </table>
            @show
        </x-slot:right-block>
    </x-flux::print.first-page-header>
@show
<main>
    @section('header')
        <div
            style="
                font-size: 12px;
                line-height: 16px;
                padding-top: 40px;
                padding-bottom: 16px;
            "
        >
            {{ render_editor_blade($model->header, ['order' => $model]) }}
            @if($model->orderType?->document_header)
                {{ render_editor_blade($model->orderType->document_header, ['order' => $model]) }}
            @endif
        </div>
    @show
    <div style="padding-bottom: 24px">
        @section('positions')
            <table style="width: 100%; table-layout: auto; font-size: 12px">
                <thead>
                    @section('positions.header')
                        <tr style="padding-top: 8px; padding-bottom: 8px">
                            <th
                                style="
                                    padding-top: 8px;
                                    padding-bottom: 8px;
                                    padding-right: 32px;
                                    text-align: left;
                                    font-weight: 400;
                                    border-bottom: 2px solid black;
                                "
                            >
                                {{ __('Pos.') }}
                            </th>
                            <th
                                style="
                                    padding-top: 8px;
                                    padding-bottom: 8px;
                                    padding-right: 32px;
                                    text-align: left;
                                    font-weight: 400;
                                    border-bottom: 2px solid black;
                                "
                            >
                                {{ __('Name') }}
                            </th>
                            <th
                                style="
                                    padding-top: 8px;
                                    padding-bottom: 8px;
                                    padding-right: 32px;
                                    text-align: center;
                                    font-weight: 400;
                                    border-bottom: 2px solid black;
                                "
                            >
                                {{ __('Amount') }}
                            </th>
                            <th
                                style="
                                    padding-top: 8px;
                                    padding-bottom: 8px;
                                    text-align: right;
                                    font-weight: 400;
                                    text-transform: uppercase;
                                    border-bottom: 2px solid black;
                                "
                            >
                                {{ __('Sum') }}
                            </th>
                        </tr>
                    @show
                </thead>
                @section('positions.positions')
                    @foreach($model->orderPositions as $position)
                        <x-flux::print.order.order-position
                            :position="$position"
                            :is-net="$isNet"
                            :loop="$loop"
                        />
                    @endforeach

                @show
            </table>
        @show
    </div>
    @if($summary)
        @section('summary')
            <div style="padding-bottom: 24px">
                <table style="width: 100%; font-size: 12px">
                    <tbody style="break-inside: avoid">
                        <tr>
                            <td
                                colspan="3"
                                style="
                                    border-bottom: 1px solid black;
                                    font-weight: 600;
                                "
                            >
                                {{ __('Summary') }}
                            </td>
                        </tr>
                        @foreach($summary as $summaryItem)
                            <tr>
                                <td>{{ $summaryItem->slug_position }}</td>
                                <td style="white-space: nowrap">
                                    {{ $summaryItem->name }}
                                </td>
                                <td style="float: right; text-align: right">
                                    {{ Number::currency($summaryItem->total_net_price ?? 0) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @show
    @endif

    @section('total')
        <table
            style="
                width: 100%;
                break-inside: avoid;
                padding-bottom: 64px;
                font-size: 12px;
                page-break-inside: avoid;
            "
        >
            <tbody style="page-break-inside: avoid">
                <tr>
                    <td
                        colspan="2"
                        style="border-bottom: 2px solid black; font-weight: 600"
                    >
                        {{ __('Total') }}
                    </td>
                </tr>
                @section('total.discounts')
                    @if(bccomp($model->total_base_net_price ?? 0, $model->total_net_price ?? 0) !== 0)
                        <tr>
                            <td style="text-align: right">
                                {{ __('Sum net without discount') }}
                            </td>
                            <td
                                style="
                                    width: 0;
                                    padding-left: 48px;
                                    text-align: right;
                                    white-space: nowrap;
                                "
                            >
                                {{ Number::currency($model->total_base_net_price) }}
                            </td>
                        </tr>
                        @if(bccomp($model->total_position_discount_percentage ?? 0, 0) !== 0)
                            <tr>
                                <td style="text-align: right">
                                    <span>{{ __('Position discounts') }}</span>
                                    <span>
                                        {{ Number::percentage(bcmul($model->total_position_discount_percentage ?? 0, 100), maxPrecision: 2) }}
                                    </span>
                                </td>
                                <td
                                    style="
                                        width: 0;
                                        padding-left: 48px;
                                        text-align: right;
                                        white-space: nowrap;
                                    "
                                >
                                    {{ Number::currency(bcmul($model->total_position_discount_flat ?? 0, -1)) }}
                                </td>
                            </tr>
                            @if($model->discounts->isNotEmpty())
                                <tr>
                                    <td style="text-align: right">
                                        {{ __('Sum net discounted') }}
                                    </td>
                                    <td
                                        style="
                                            width: 0;
                                            padding-left: 48px;
                                            text-align: right;
                                            white-space: nowrap;
                                        "
                                    >
                                        {{ Number::currency($model->total_base_discounted_net_price ?? 0) }}
                                    </td>
                                </tr>
                            @endif
                        @endif
                        @foreach($model->discounts as $discount)
                            <tr>
                                <td style="text-align: right">
                                    <span>
                                        {{ data_get($discount, 'name', __('Head discount')) }}
                                    </span>
                                    <span>
                                        {{ Number::percentage(bcmul(data_get($discount, 'discount_percentage', 0), 100), maxPrecision: 2) }}
                                    </span>
                                </td>
                                <td
                                    style="
                                        width: 0;
                                        padding-left: 48px;
                                        text-align: right;
                                        white-space: nowrap;
                                    "
                                >
                                    {{ Number::currency(bcmul(data_get($discount, 'discount_flat', 0), -1)) }}
                                </td>
                            </tr>
                        @endforeach
                        <tr>
                            <td
                                colspan="2"
                                style="border-bottom: 1px solid black"
                            ></td>
                        </tr>
                    @endif

                @show
                @section('total.net')
                    <tr>
                        <td style="text-align: right">{{ __('Sum net') }}</td>
                        <td
                            style="
                                width: 0;
                                padding-left: 48px;
                                text-align: right;
                                white-space: nowrap;
                            "
                        >
                            {{ Number::currency($model->total_net_price) }}
                        </td>
                    </tr>
                @show
                @section('total.vats')
                    @foreach($model->total_vats ?? [] as $vat)
                        <tr>
                            <td style="text-align: right">
                                {{
                            __('Plus :percentage VAT from :total_net', [
                                'percentage' => Number::percentage(bcmul($vat['vat_rate_percentage'], 100), maxPrecision: 2),
                                'total_net' => Number::currency($vat['total_net_price']),
                            ])
                        }}
                            </td>
                            <td
                                style="
                                    width: 0;
                                    padding-left: 48px;
                                    text-align: right;
                                    white-space: nowrap;
                                "
                            >
                                {{ Number::currency($vat['total_vat_price']) }}
                            </td>
                        </tr>
                    @endforeach

                @show
                @section('total.gross')
                    <tr style="font-weight: 700">
                        <td style="text-align: right">
                            {{ __('Total Gross') }}
                        </td>
                        <td
                            style="
                                width: 0;
                                padding-left: 48px;
                                text-align: right;
                                white-space: nowrap;
                            "
                        >
                            {{ Number::currency($model->total_gross_price) }}
                        </td>
                    </tr>
                @show
            </tbody>
        </table>
    @show
    @section('footer')
        <div style="font-size: 12px; line-height: 16px; break-inside: avoid">
            {{ render_editor_blade($model->footer, ['order' => $model]) }}
            @if($model->orderType?->document_footer)
                {{ render_editor_blade($model->orderType->document_footer, ['order' => $model]) }}
            @endif

            {!!
            $model
                ->vatRates()
                ->distinct()
                ->get()
                ->each->localize($model->language_id)
                ->pluck('footer_text')
                ->filter()
                ->map(fn (string $text) => render_editor_blade($text, ['order' => $model])->toHtml())
                ->pipe(fn (\Illuminate\Support\Collection $items) => $items->isNotEmpty() ? '<br>' . $items->implode('<br>') : '')
        !!}
        </div>
    @show
</main>
