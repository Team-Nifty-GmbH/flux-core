@extends('print::order.order')

@section('first-page-right-block.rows')
    @parent
    <tr>
        <td class="text-right font-semibold">{{ __('Invoice Date') }}:</td>
        <td>
            {{ ($model->invoice_date ?: now())->locale(app()->getLocale())->isoFormat('L') }}
        </td>
    </tr>
    <tr>
        <td class="text-right font-semibold">{{ __('Performance Date') }}:</td>
        <td>
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
    @parent
    <div>
        {!! Blade::render(html_entity_decode($model->paymentType->description ?? ''), ['model' => $model]) !!}
    </div>
@endsection
