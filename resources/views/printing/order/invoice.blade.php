@extends('flux::printing.order.order')
@section('first-page-right-block.labels')
    @parent
    <div class="font-semibold">
        {{ __('Invoice Date') }}:
    </div>
    <div class="font-semibold">
        {{ __('Performance Date') }}:
    </div>
@endsection
@section('first-page-right-block.values')
    @parent
    <div>
        {{ $model->invoice_date->locale(app()->getLocale())->isoFormat('L') }}
    </div>
    <div>
        {{ $model->system_delivery_date->locale(app()->getLocale())->isoFormat('L') }}
    </div>
@endsection
