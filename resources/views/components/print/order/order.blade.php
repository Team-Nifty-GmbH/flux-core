@use(Illuminate\Support\Number)
<tbody class="bg-uneven">
    <tr>
        <td class="pos py-4 align-top">
            {{ $order->order_number }}
        </td>
        <td class="py-4 align-top">
            {{ $order->invoice_date->locale(app()->getLocale())->isoFormat('L') }}
        </td>
        <td class="pos py-4 align-top">
            {{ $order->invoice_number }}
        </td>
        <td class="py-4 text-right align-top">
            {{ Number::currency($order->total_gross_price) }}
        </td>
        <td class="py-4 text-right align-top">
            @if ($order instanceof Illuminate\Support\Fluent)
                {{ Number::currency($order->total_paid) }}
            @else
                {{ Number::currency($order->totalPaid()) }}
            @endif
        </td>
        <td class="py-4 text-right align-top">
            {{ Number::currency($order->balance) }}
        </td>
    </tr>
</tbody>
