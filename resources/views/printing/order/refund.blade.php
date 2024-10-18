@extends('flux::printing.order.order')
@section('first-page-right-block.labels')
    @parent
    <div class="font-semibold">
        {{ __('Refund Date') }}:
    </div>
    <div class="font-semibold">
        {{ __('Related Invoice Number') }}:
    </div>
@endsection
@section('first-page-right-block.values')
    @parent
    <div>
        {{ ($model->invoice_date ?: now())->locale(app()->getLocale())->isoFormat('L') }}
    </div>
    <div>
        {{ $model->parent?->invoice_number }}
    </div>
@endsection
@section('total')
    @parent
    <div>
        {!! $model->paymentType->description !!}
    </div>
@endsection
