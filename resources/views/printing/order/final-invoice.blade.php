@extends('flux::printing.order.order')

@section('first-page-right-block.rows')
    @parent
    <tr>
        <td style="padding-top: 0; padding-bottom: 0; text-align: left; font-weight: 600;">{{ __('Invoice Date') }}:</td>
        <td style="padding: 0; text-align: right;">
            {{ ($model->invoice_date ?: now())->locale(app()->getLocale())->isoFormat('L') }}
        </td>
    </tr>
    <tr>
        <td style="padding-top: 0; padding-bottom: 0; text-align: left; font-weight: 600;">
            {{ __('Performance Date') }}:
        </td>
        <td style="padding: 0; text-align: right;">
            @if ($model->system_delivery_date_end && $model->system_delivery_date_end->format('Y-m-d') !== $model->system_delivery_date->format('Y-m-d'))
                {{ ($model->system_delivery_date ?: now())->locale(app()->getLocale())->isoFormat('L') }}
                -
                {{ ($model->system_delivery_date_end ?: now())->locale(app()->getLocale())->isoFormat('L') }}
            @else
                {{ ($model->system_delivery_date ?: now())->locale(app()->getLocale())->isoFormat('L') }}
            @endif
        </td>
    </tr>
@endsection

@section('total')
    <table style="width: 100%; padding-bottom: 64px; font-size: 12px; border-collapse: collapse; break-inside: avoid; page-break-inside: avoid;">
        <tbody style="page-break-inside: avoid;">
        <tr>
            <td colspan="2" style="border-bottom: 2px solid black; font-weight: 600;">
                {{ __('Total') }}
            </td>
        </tr>
        @section('total.subtotal')
            <tr>
                <td style="text-align: right;">
                    {{ __('Subtotal net') }}
                </td>
                <td style="width: 0; white-space: nowrap; padding-left: 48px; text-align: right;">
                    {{ Number::currency($model->subtotal_net_price) }}
                </td>
            </tr>
        @show
        @section('total.subtotal.vats')
            @foreach ($model->subtotal_vats ?? [] as $subTotalVat)
                <tr>
                    <td style="text-align: right;">
                        {{
                            __('Plus :percentage VAT from :total_net', [
                                'percentage' => Number::percentage(bcmul($subTotalVat['vat_rate_percentage'], 100)),
                                'total_net' => Number::currency($subTotalVat['total_net_price']),
                            ])
                        }}
                    </td>
                    <td style="width: 0; white-space: nowrap; padding-left: 48px; text-align: right;">
                        {{ Number::currency($subTotalVat['total_vat_price']) }}
                    </td>
                </tr>
                <tr><td colspan="2" style="border-bottom: 1px solid black;"></td></tr>
            @endforeach
        @show
        @section('total.children')
            @if ($model->children->isNotEmpty())
                @foreach ($model->children as $child)
                    <tr>
                        <td style="text-align: right; font-weight: 600;">
                            {{ $child->getLabel() }}
                        </td>
                        <td style="width: 0; white-space: nowrap; padding-left: 48px; text-align: right;">
                            {{ Number::currency(bcmul($child->total_net_price, '-1')) }}
                        </td>
                    </tr>
                    @foreach ($child->total_vats ?? [] as $childVat)
                        <tr>
                            <td style="text-align: right;">
                                {{
                                    __('Plus :percentage VAT from :total_net', [
                                        'percentage' => Number::percentage(bcmul($childVat['vat_rate_percentage'], 100)),
                                        'total_net' => Number::currency(bcmul($childVat['total_net_price'], '-1')),
                                    ])
                                }}
                            </td>
                            <td style="width: 0; white-space: nowrap; padding-left: 48px; text-align: right;">
                                {{ Number::currency(bcmul($childVat['total_vat_price'], '-1')) }}
                            </td>
                        </tr>
                    @endforeach
                @endforeach

                <tr><td colspan="2" style="border-bottom: 1px solid black;"></td></tr>
            @endif
        @show
        @section('total.net')
            <tr>
                <td style="text-align: right;">
                    {{ __('Sum net') }}
                </td>
                <td style="width: 0; white-space: nowrap; padding-left: 48px; text-align: right;">
                    {{ Number::currency($model->total_net_price) }}
                </td>
            </tr>
        @show
        @section('total.net.vats')
            @foreach ($model->total_vats ?? [] as $totalVat)
                <tr>
                    <td style="text-align: right;">
                        {{
                            __('Plus :percentage VAT from :total_net', [
                                'percentage' => Number::percentage(bcmul($totalVat['vat_rate_percentage'], 100)),
                                'total_net' => Number::currency($totalVat['total_net_price']),
                            ])
                        }}
                    </td>
                    <td style="width: 0; white-space: nowrap; padding-left: 48px; text-align: right;">
                        {{ Number::currency($totalVat['total_vat_price']) }}
                    </td>
                </tr>
            @endforeach

            <tr><td colspan="2" style="border-bottom: 1px solid black;"></td></tr>
        @show
        @section('total.gross')
            <tr style="font-weight: 700;">
                <td style="text-align: right;">
                    {{ __('Total Gross') }}
                </td>
                <td style="width: 0; white-space: nowrap; padding-left: 48px; text-align: right;">
                    {{ Number::currency($model->total_gross_price) }}
                </td>
            </tr>
        @show
        </tbody>
    </table>
    <div>
        {{ render_editor_blade($model->paymentType()->withTrashed()->value('description'), ['model' => $model]) }}
    </div>
@endsection
