@extends('flux::printing.order.order')

@section('first-page-right-block.rows')
    @parent
    <tr>
        <td
            style="
                padding-top: 0;
                padding-bottom: 0;
                text-align: left;
                font-weight: 600;
            "
        >
            {{ __('Invoice Date') }}:
        </td>
        <td style="padding: 0; text-align: right">
            {{ ($model->invoice_date ?: now())->locale(app()->getLocale())->isoFormat('L') }}
        </td>
    </tr>
    <tr>
        <td
            style="
                padding-top: 0;
                padding-bottom: 0;
                text-align: left;
                font-weight: 600;
            "
        >
            {{ __('Performance Date') }}:
        </td>
        <td style="padding: 0; text-align: right">
            @if($model->system_delivery_date_end && $model->system_delivery_date_end->format('Y-m-d') !== $model->system_delivery_date->format('Y-m-d'))
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
    @parent
    <div>
        {{ render_editor_blade($model->paymentType()->withTrashed()->value('description'),['model' => $model],) }}
    </div>
@endsection
