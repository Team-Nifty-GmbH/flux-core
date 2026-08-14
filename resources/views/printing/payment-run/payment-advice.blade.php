@use(\Illuminate\Support\Fluent)
@use(\Illuminate\Support\Number)
@php
    $paymentRun = $model->paymentRun;
    $sign = $paymentRun->payment_run_type_enum->expectedSign();
    $address = $model->contact?->invoiceAddress ?: $model->contact?->mainAddress;
@endphp
<x-flux::print.first-page-header
    :address="Fluent::make($address?->toArray() ?? ['company' => $model->account_holder])"
    :subject="$subject"
>
    <x-slot:right-block>
        <table style="border-collapse: separate; border-spacing: 8px 0">
            <tbody
                style="
                    vertical-align: text-top;
                    font-size: 12px;
                    line-height: 1;
                "
            >
                <tr>
                    <td style="padding: 0; text-align: left; font-weight: 600">
                        {{ __('Iban') }}
                    </td>
                    <td style="padding: 0; text-align: right">
                        {{ $model->iban }}
                    </td>
                </tr>
                <tr>
                    <td style="padding: 0; text-align: left; font-weight: 600">
                        {{ __('Reference') }}
                    </td>
                    <td style="padding: 0; text-align: right">
                        {{ $model->end_to_end_id }}
                    </td>
                </tr>
                <tr>
                    <td style="padding: 0; text-align: left; font-weight: 600">
                        {{ __('Execution Date') }}
                    </td>
                    <td style="padding: 0; text-align: right">
                        {{ ($paymentRun->instructed_execution_date ?: now())->locale(app()->getLocale())->isoFormat('L') }}
                    </td>
                </tr>
            </tbody>
        </table>
    </x-slot:right-block>
</x-flux::print.first-page-header>
<main>
    <table
        style="
            width: 100%;
            table-layout: auto;
            font-size: 12px;
            border-collapse: collapse;
        "
    >
        <thead>
            <tr>
                <th
                    style="
                        padding: 8px 32px 8px 0;
                        text-align: left;
                        font-weight: 400;
                        border-bottom: 2px solid black;
                    "
                >
                    {{ __('Number') }}
                </th>
                <th
                    style="
                        padding: 8px 32px 8px 0;
                        text-align: left;
                        font-weight: 400;
                        border-bottom: 2px solid black;
                    "
                >
                    {{ __('Date') }}
                </th>
                <th
                    style="
                        padding: 8px 32px 8px 0;
                        text-align: right;
                        font-weight: 400;
                        border-bottom: 2px solid black;
                    "
                >
                    {{ __('Gross Amount') }}
                </th>
                <th
                    style="
                        padding: 8px 0;
                        text-align: right;
                        font-weight: 400;
                        border-bottom: 2px solid black;
                    "
                >
                    {{ __('Applied Amount') }}
                </th>
            </tr>
        </thead>
        <tbody>
            @foreach ($model->orders as $order)
                @php
                    $applied = bcmul((string) $order->pivot->amount, (string) $sign, 2);
                    $isCredit = bccomp($applied, '0', 2) < 0;
                @endphp
                <tr>
                    <td style="padding: 4px 32px 4px 0">
                        {{ $order->invoice_number }}
                        @if ($isCredit)
                            <span style="color: #b91c1c"
                                >({{ __('Credit note') }})</span
                            >
                        @endif
                    </td>
                    <td style="padding: 4px 32px 4px 0">
                        {{ ($order->invoice_date ?: $order->created_at)->locale(app()->getLocale())->isoFormat('L') }}
                    </td>
                    <td style="padding: 4px 32px 4px 0; text-align: right">
                        {{ Number::currency($order->total_gross_price) }}
                    </td>
                    <td style="padding: 4px 0; text-align: right">
                        {{ Number::currency($applied) }}
                    </td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="font-weight: 700">
                <td
                    colspan="3"
                    style="
                        padding-top: 8px;
                        text-align: right;
                        border-top: 2px solid black;
                    "
                >
                    {{ __('Transfer Amount') }}
                </td>
                <td
                    style="
                        padding-top: 8px;
                        text-align: right;
                        border-top: 2px solid black;
                    "
                >
                    {{ Number::currency(bcmul((string) $model->amount, (string) $sign, 2)) }}
                </td>
            </tr>
        </tfoot>
    </table>
</main>
