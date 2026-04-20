@use(Illuminate\Support\Number)
<tbody class="bg-uneven">
    <tr>
        <td
            class="pos"
            style="padding-top: 16px; padding-bottom: 16px; vertical-align: top"
        >
            {{ $order->order_number }}
        </td>
        <td
            style="padding-top: 16px; padding-bottom: 16px; vertical-align: top"
        >
            {{ $order->invoice_date->locale(app()->getLocale())->isoFormat('L') }}
        </td>
        <td
            class="pos"
            style="padding-top: 16px; padding-bottom: 16px; vertical-align: top"
        >
            {{ $order->invoice_number }}
        </td>
        <td
            style="
                padding-top: 16px;
                padding-bottom: 16px;
                text-align: right;
                vertical-align: top;
            "
        >
            {{ Number::currency($order->total_gross_price) }}
        </td>
        <td
            style="
                padding-top: 16px;
                padding-bottom: 16px;
                text-align: right;
                vertical-align: top;
            "
        >
            {{ Number::currency($order->totalPaid()) }}
        </td>
        <td
            style="
                padding-top: 16px;
                padding-bottom: 16px;
                text-align: right;
                vertical-align: top;
            "
        >
            {{ Number::currency($order->balance) }}
        </td>
    </tr>
</tbody>
