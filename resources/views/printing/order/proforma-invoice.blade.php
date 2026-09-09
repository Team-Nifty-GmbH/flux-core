@extends('flux::printing.order.order')
@use(\Illuminate\Support\Number)

@section('first-page-right-block.rows')
    @parent
    <tr style="line-height: 1">
        <td
            style="
                padding-top: 0;
                padding-bottom: 0;
                text-align: left;
                font-weight: 600;
            "
        >
            {{ __('Expected Delivery Date') }}:
        </td>
        <td style="padding-top: 0; padding-bottom: 0; text-align: right">
            @if (! $model->system_delivery_date)
                {{ __('Not yet determined') }}
            @elseif ($model->system_delivery_date_end && $model->system_delivery_date_end->format('Y-m-d') !== $model->system_delivery_date->format('Y-m-d'))
                {{ $model->system_delivery_date->locale(app()->getLocale())->isoFormat('L') }}
                &ndash;
                {{ $model->system_delivery_date_end->locale(app()->getLocale())->isoFormat('L') }}
            @else
                {{ $model->system_delivery_date->locale(app()->getLocale())->isoFormat('L') }}
            @endif
        </td>
    </tr>
@endsection

@section('header')
    <div
        style="
            margin-top: 32px;
            border: 1px solid black;
            padding: 8px;
            font-size: 12px;
            line-height: 16px;
            font-weight: 600;
        "
    >
        {{ __('This document is not an invoice and does not entitle to input tax deduction.') }}
    </div>
    @parent
@endsection

@section('summary')
@endsection

@section('total.discounts')
    @if (bccomp($model->total_base_discounted_net_price ?? 0, $model->total_net_price ?? 0) !== 0)
        <tr>
            <td style="text-align: right">{{ __('Discount') }}</td>
            <td
                style="
                    width: 0;
                    padding-left: 48px;
                    text-align: right;
                    white-space: nowrap;
                "
            >
                {{ Number::percentage(bcmul($model->discount_percentage, 100), maxPrecision: 2) }}
            </td>
        </tr>
    @endif
@endsection

@section('total.net')
@endsection

@section('total.vats')
@endsection

@section('total.gross')
    <tr style="font-weight: 700">
        <td style="text-align: right">{{ __('Total value') }}</td>
        <td
            style="
                width: 0;
                padding-left: 48px;
                text-align: right;
                white-space: nowrap;
            "
        >
            {{ Number::currency($isNet ? $model->total_net_price : $model->total_gross_price) }}
        </td>
    </tr>
@endsection
