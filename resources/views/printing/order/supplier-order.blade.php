@extends('flux::printing.order.order')

@section('order-print.delivery-address')
    <div style="padding-top: 40px; font-size: 12px; line-height: 16px">
        <div style="font-weight: 600">{{ __('Delivery Address') }}</div>
        <address style="font-style: normal">
            @foreach (array_filter([$tenant->name, $tenant->street, trim($tenant->postcode . ' ' . $tenant->city)]) as $line)
                <div>{{ $line }}</div>
            @endforeach
        </address>
    </div>
@endsection
