<x-mail::message>
    {{ __('Thank you for your order!')}}

    {{ __('We received your order dated :order_date', ['order_date' => $order->order_date]) }}

    <x-mail::table>
        |  | {{ __('Product number') }} | {{ __('Name') }}       | {{ __('Amount') }}         | {{ __('Total') }}  |
        | :-------------  | :------------- | :------------- |:-------------:| --------:|
        @foreach($order->orderPositions as $orderPosition)
            | <img src="{{ ($orderPosition->product?->cover_image ?? $orderPosition->product?->parent?->cover_image)?->getUrl('thumb') ?? route('icons', ['name' => 'photo']) }}" style="width: 100px; height: 100px;" alt="Product Image"> | {{ $orderPosition->product_number }} | {{ $orderPosition->name }}| {{ $orderPosition->amount ? bcround($orderPosition->amount, 2) : '' }}| {{ $orderPosition->total_net_price ? Number::currency($orderPosition->total_net_price, $orderPosition->currency->iso, app()->getLocale()) : '' }} |
        @endforeach
    </x-mail::table>

    **{{ __('Total Net') }}:** {{ Number::currency($order->total_net_price, $order->currency->iso, app()->getLocale()) }}
    @foreach($order->total_vats ?? [] as $vat)
        **{{ __('Plus ') }}** {{ format_number($vat['vat_rate_percentage'], NumberFormatter::PERCENT) }} {{ Number::currency($vat['total_vat_price'], $order->currency->iso, app()->getLocale())}}
    @endforeach
    **{{ __('Total Gross') }}:** {{ Number::currency($order->total_gross_price, $order->currency->iso, app()->getLocale()) }}

    **{{ __('Invoice Address') }}**
</x-mail::message>
