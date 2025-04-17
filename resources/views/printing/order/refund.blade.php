@extends('flux::printing.order.order')
@section('first-page-right-block.rows')
    @parent
    <tr>
        <td class="text-right font-semibold">{{ __('Refund Date') }}:</td>
        <td>
            {{ ($model->invoice_date ?: now())->locale(app()->getLocale())->isoFormat('L') }}
        </td>
    </tr>
    <tr>
        <td class="text-right font-semibold">
            {{ __('Related Invoice Number') }}:
        </td>
        <td>
            {{ $model->parent?->invoice_number }}
        </td>
    </tr>
@endsection

@section('total')
    @parent
    <div>
        {!! $model->paymentType->description !!}
    </div>
@endsection
