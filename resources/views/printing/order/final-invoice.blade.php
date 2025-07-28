@extends('flux::printing.order.order')
@php
    $totalNetPrice = $model->total_net_price;
    $totalVats = \Illuminate\Support\Arr::keyBy($model->total_vats ?? [], 'vat_rate_percentage');

    foreach ($model->children as $child) {
        $totalNetPrice = bcsub($totalNetPrice, $child->total_net_price);
        foreach ($child->total_vats as $childVat) {
            data_set(
                $totalVats[data_get($childVat, 'vat_rate_percentage')],
                'total_vat_price',
                bcsub(
                    data_get($totalVats[data_get($childVat, 'vat_rate_percentage')], 'total_vat_price') ?? 0,
                    data_get($childVat, 'total_vat_price') ?? 0,
                )
            );
            data_set(
                $totalVats[data_get($childVat, 'vat_rate_percentage')],
                'total_net_price',
                bcsub(
                    data_get($totalVats[data_get($childVat, 'vat_rate_percentage')], 'total_net_price') ?? 0,
                    data_get($childVat, 'total_net_price') ?? 0,
                )
            );
        }
    }

    $totalGross = bcadd(
        $totalNetPrice,
        array_reduce(
            $totalVats,
            function ($carry, $vat) {
                return bcadd($carry, data_get($vat, 'total_vat_price') ?? 0);
            },
            0
        )
    );
@endphp

@section('first-page-right-block.rows')
    @parent
    <tr>
        <td class="py-0 text-left font-semibold">{{ __('Invoice Date') }}:</td>
        <td class="p-0 text-right">
            {{ ($model->invoice_date ?: now())->locale(app()->getLocale())->isoFormat('L') }}
        </td>
    </tr>
    <tr>
        <td class="py-0 text-left font-semibold">
            {{ __('Performance Date') }}:
        </td>
        <td class="p-0 text-right">
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
    <table class="w-full pb-16 text-xs">
        <tbody class="break-inside-avoid">
        <tr>
            <td colspan="2" class="border-b-2 border-black font-semibold">
                {{ __('Total') }}
            </td>
        </tr>
        @section('total.subtotal')
            <tr>
                <td class="text-right">
                    {{ __('Subtotal net') }}
                </td>
                <td class="w-0 whitespace-nowrap pl-12 text-right">
                    {{ Number::currency($model->total_net_price) }}
                </td>
            </tr>
        @show
        @section('total.subtotal.vats')
            @foreach ($model->total_vats ?? [] as $vat)
                <tr>
                    <td class="text-right">
                        {{
                            __('Plus :percentage VAT from :total_net', [
                                'percentage' => Number::percentage(bcmul($vat['vat_rate_percentage'], 100)),
                                'total_net' => Number::currency($vat['total_net_price']),
                            ])
                        }}
                    </td>
                    <td class="w-0 whitespace-nowrap pl-12 text-right">
                        {{ Number::currency($vat['total_vat_price']) }}
                    </td>
                </tr>
                <tr class="border-b"></tr>
            @endforeach
        @show
        @section('total.children')
            @if ($model->children->isNotEmpty())
                @foreach ($model->children as $child)
                    <tr>
                        <td class="text-right font-semibold">
                            {{ $child->getLabel() }}
                        </td>
                        <td class="w-0 whitespace-nowrap pl-12 text-right">
                            {{ Number::currency(bcmul($child->total_net_price, '-1')) }}
                        </td>
                    </tr>
                    @section('total.child.vats')
                        @foreach ($child->total_vats ?? [] as $childVat)
                            <tr>
                                <td class="text-right">
                                    {{
                                        __('Plus :percentage VAT from :total_net', [
                                            'percentage' => Number::percentage(bcmul($childVat['vat_rate_percentage'], 100)),
                                            'total_net' => Number::currency(bcmul($childVat['total_net_price'], '-1')),
                                        ])
                                    }}
                                </td>
                                <td class="w-0 whitespace-nowrap pl-12 text-right">
                                    {{ Number::currency(bcmul($childVat['total_vat_price'], '-1')) }}
                                </td>
                            </tr>
                        @endforeach
                    @show
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
                    {{ Number::currency($totalNetPrice) }}
                </td>
            </tr>
        @show
        @section('total.net.vats')
            @foreach ($totalVats ?? [] as $totalVat)
                <tr>
                    <td class="text-right">
                        {{
                            __('Plus :percentage VAT from :total_net', [
                                'percentage' => Number::percentage(bcmul($totalVat['vat_rate_percentage'], 100)),
                                'total_net' => Number::currency($totalVat['total_net_price']),
                            ])
                        }}
                    </td>
                    <td class="w-0 whitespace-nowrap pl-12 text-right">
                        {{ Number::currency($totalVat['total_vat_price']) }}
                    </td>
                </tr>
            @endforeach

            <tr class="border-b"></tr>
        @show
        @section('total.gross')
            <tr class="font-bold">
                <td class="text-right">
                    {{ __('Total Gross') }}
                </td>
                <td class="w-0 whitespace-nowrap pl-12 text-right">
                    {{ Number::currency($totalGross) }}
                </td>
            </tr>
        @show
        </tbody>
    </table>
    <div>
        {!!
            Blade::render(
                html_entity_decode(
                    $model
                        ->paymentType()
                        ->withTrashed()
                        ->value('description') ?? '',
                ),
                ['model' => $model],
            )
        !!}
    </div>
@endsection
