@use(Illuminate\Support\Number)
<x-flux::print.first-page-header
    :address="$model->invoiceAddress ?? $model->mainAddress"
>
    <x-slot:right-block>
        <div style="display: inline-block">
            @section('first-page-right-block')
                <div style="display: inline-block">
                    @section('first-page-right-block.labels')
                        <div style="font-weight: 600">
                            {{ __('Customer no.') }}:
                        </div>
                        <div style="font-weight: 600">{{ __('Date') }}:</div>
                    @show
                </div>
                <div
                    style="
                        display: inline-block;
                        padding-left: 24px;
                        text-align: right;
                    "
                >
                    @section('first-page-right-block.values')
                        <div>{{ $model->customer_number }}</div>
                        <div>
                            {{ now()->locale(app()->getLocale())->isoFormat('L') }}
                        </div>
                    @show
                </div>
            @show
        </div>
    </x-slot:right-block>
</x-flux::print.first-page-header>
<main>
    <div
        style="
            font-size: 12px;
            line-height: 16px;
            padding-top: 40px;
            padding-bottom: 16px;
        "
    >
        {!! $model->header !!}
    </div>
    <div style="padding-bottom: 24px">
        @section('positions')
            <table style="width: 100%; table-layout: auto; font-size: 12px">
                <thead>
                    @section('positions.header')
                        <tr>
                            <th
                                style="
                                    text-align: left;
                                    font-weight: 400;
                                    border-bottom: 2px solid black;
                                "
                            >
                                {{ __('Order no.') }}
                            </th>
                            <th
                                style="
                                    text-align: left;
                                    font-weight: 400;
                                    border-bottom: 2px solid black;
                                "
                            >
                                {{ __('Date') }}
                            </th>
                            <th
                                style="
                                    text-align: left;
                                    font-weight: 400;
                                    border-bottom: 2px solid black;
                                "
                            >
                                {{ __('Invoice no.') }}
                            </th>
                            <th
                                style="
                                    text-align: right;
                                    font-weight: 400;
                                    text-transform: uppercase;
                                    border-bottom: 2px solid black;
                                "
                            >
                                {{ __('Total Gross') }}
                            </th>
                            <th
                                style="
                                    text-align: right;
                                    font-weight: 400;
                                    text-transform: uppercase;
                                    border-bottom: 2px solid black;
                                "
                            >
                                {{ __('Payments') }}
                            </th>
                            <th
                                style="
                                    text-align: right;
                                    font-weight: 600;
                                    text-transform: uppercase;
                                    border-bottom: 2px solid black;
                                "
                            >
                                {{ __('Balance') }}
                            </th>
                        </tr>
                    @show
                </thead>
                @section('positions.body')
                    @foreach($model->orders()->unpaid()->get(['id', 'order_number', 'invoice_date', 'invoice_number', 'total_gross_price', 'balance']) as $order)
                        <x-flux::print.order.order
                            :order="$order"
                            :loop="$loop"
                        />
                    @endforeach

                @show
            </table>
        @show
    </div>
    <div style="padding-bottom: 24px">
        @section('total')
            <table style="width: 100%">
                <tbody style="break-inside: avoid">
                    <tr>
                        <td
                            colspan="3"
                            style="
                                border-bottom: 1px solid black;
                                font-weight: 600;
                            "
                        >
                            {{ __('Total') }}
                        </td>
                        <td
                            style="
                                float: right;
                                border-bottom: 1px solid black;
                                text-align: right;
                                font-weight: 600;
                            "
                        >
                            {{ Number::currency($model->orders()->unpaid()->sum('balance'),) }}
                        </td>
                    </tr>
                </tbody>
            </table>
        @show
    </div>
</main>
