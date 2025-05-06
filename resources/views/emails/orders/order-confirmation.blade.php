<x-mail::message>
{{ __('Thank you for your order!')}}

{{ __('We received your order dated :order_date.', ['order_date' => $order->order_date->isoFormat('L')]) }}

<x-mail::table>
|  | {{ __('Product number') }} | {{ __('Name') }}       | {{ __('Amount') }}         | {{ __('Total') }}  |
| :-------------  | :------------- | :------------- |:-------------:| --------:|
@foreach($order->orderPositions as $orderPosition)
| <img src="{{ ($orderPosition->product?->cover_image ?? $orderPosition->product?->parent?->cover_image)?->getUrl('thumb') ?? route('icons', ['name' => 'photo']) }}" style="width: 100px; height: 100px;" alt="Product Image"> | {{ $orderPosition->product_number }} | {{ $orderPosition->name }}| {{ $orderPosition->amount ? bcround($orderPosition->amount, 2) : '' }}| {{ $orderPosition->total_net_price ? Number::currency($orderPosition->total_net_price, $orderPosition->currency->iso, app()->getLocale()) : '' }} |
@endforeach
</x-mail::table>

<x-mail::panel>
**{{ __('Total Net') }}:** {{ Number::currency($order->total_net_price, $order->currency->iso, app()->getLocale()) }}<br>
@foreach($order->total_vats ?? [] as $vat)
**{{ __('Plus ') }}:** {{ Number::percentage(bcmul($vat['vat_rate_percentage'], 100)) }} {{ Number::currency($vat['total_vat_price'], $order->currency->iso, app()->getLocale())}}<br>
@endforeach
**{{ __('Total Gross') }}:** {{ Number::currency($order->total_gross_price, $order->currency->iso, app()->getLocale()) }}<br>

**{{ __('Invoice Address') }}**<br>
{{ data_get($order->address_invoice, 'company') }}<br>
{{ data_get($order->address_invoice, 'firstname') }} {{ data_get($order->address_invoice, 'lastname') }}<br>
{{ data_get($order->address_invoice, 'addition') }}<br>
{{ data_get($order->address_invoice, 'street') }}<br>
{{ data_get($order->address_invoice, 'zip') }} {{ data_get($order->address_invoice, 'city') }}<br>
{{ data_get($order->address_invoice, 'country') }}<br>

**{{ __('Delivery Address') }}**<br>
{{ data_get($order->address_delivery, 'company') }}<br>
{{ data_get($order->address_delivery, 'firstname') }} {{ data_get($order->address_delivery, 'lastname') }}<br>
{{ data_get($order->address_delivery, 'addition') }}<br>
{{ data_get($order->address_delivery, 'street') }}<br>
{{ data_get($order->address_delivery, 'zip') }} {{ data_get($order->address_delivery, 'city') }}<br>
{{ data_get($order->address_delivery, 'country') }}<br>
</x-mail::panel>
</x-mail::message>
