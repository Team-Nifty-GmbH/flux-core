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
            {{ __('Refund Date') }}:
        </td>
        <td style="padding-top: 0; padding-bottom: 0; text-align: right">
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
            {{ __('Related Invoice Number') }}:
        </td>
        <td style="padding-top: 0; padding-bottom: 0; text-align: right">
            {{ $model->parent?->invoice_number }}
        </td>
    </tr>
@endsection

@section('total')
    @parent
    <div>
        {{ render_editor_blade($model->paymentType()->withTrashed()->value('description'),['model' => $model],) }}
    </div>
@endsection
