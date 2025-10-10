@extends('flux::printing.order.order')

@if (is_null($layout))
    @section('first-page-right-block.rows')
        @parent
        <tr>
            <td class="py-0 text-left font-semibold">
                {{ __('Refund Date') }}:
            </td>
            <td class="py-0 text-right">
                {{ ($model->invoice_date ?: now())->locale(app()->getLocale())->isoFormat('L') }}
            </td>
        </tr>
        <tr>
            <td class="py-0 text-left font-semibold">
                {{ __('Related Invoice Number') }}:
            </td>
            <td class="py-0 text-right">
                {{ $model->parent?->invoice_number }}
            </td>
        </tr>
    @endsection
@endif

@section('total')
    @parent
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
