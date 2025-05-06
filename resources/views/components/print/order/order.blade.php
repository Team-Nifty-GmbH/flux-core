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
            {{ $formatter->formatCurrency($order->total_gross_price, $currency) }}
        </td>
        <td class="py-4 text-right align-top">
            {{ $formatter->formatCurrency($order->transactions()->withPivot('amount')->sum('order_transaction.amount'), $currency) }}
        </td>
        <td class="py-4 text-right align-top">
            {{ $formatter->formatCurrency($order->balance, $currency) }}
        </td>
    </tr>
</tbody>
