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
        {{ ($model->invoice_date ?: now())->locale(app()->getLocale())->isoFormat('L') }}
    </div>
    @if($model->system_delivery_date_end)
        <div>
            {{ ($model->system_delivery_date ?: now())->locale(app()->getLocale())->isoFormat('L') }} - {{ ($model->system_delivery_date_end ?: now())->locale(app()->getLocale())->isoFormat('L') }}
        </div>
    @else
        <div>
            {{ ($model->system_delivery_date ?: now())->locale(app()->getLocale())->isoFormat('L') }}
        </div>
    @endif
@endsection
@section('total')
    @parent
    <div>
        {!! $model->paymentType->description !!}
    </div>
@endsection
