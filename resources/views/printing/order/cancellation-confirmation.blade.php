@use(\Illuminate\Support\Fluent)
@use(\FluxErp\Settings\SubscriptionSettings)

<x-flux::print.first-page-header
    :address="Fluent::make($model->address_invoice)"
    :$model
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
                <tr style="line-height: 1">
                    <td
                        style="
                            padding-top: 0;
                            padding-bottom: 0;
                            text-align: left;
                            font-weight: 600;
                        "
                    >
                        {{ __('Order no.') }}
                    </td>
                    <td
                        style="
                            padding-top: 0;
                            padding-bottom: 0;
                            text-align: right;
                        "
                    >
                        {{ $model->order_number }}
                    </td>
                </tr>
                <tr style="line-height: 1">
                    <td
                        style="
                            padding-top: 0;
                            padding-bottom: 0;
                            text-align: left;
                            font-weight: 600;
                        "
                    >
                        {{ __('Customer no.') }}
                    </td>
                    <td
                        style="
                            padding-top: 0;
                            padding-bottom: 0;
                            text-align: right;
                        "
                    >
                        {{ $model->contact()->withTrashed()->value('customer_number') }}
                    </td>
                </tr>
                <tr style="line-height: 1">
                    <td
                        style="
                            padding-top: 0;
                            padding-bottom: 0;
                            text-align: left;
                            font-weight: 600;
                        "
                    >
                        {{ __('Date') }}
                    </td>
                    <td
                        style="
                            padding-top: 0;
                            padding-bottom: 0;
                            text-align: right;
                        "
                    >
                        {{ now()->locale(app()->getLocale())->isoFormat('L') }}
                    </td>
                </tr>
            </tbody>
        </table>
    </x-slot:right-block>
</x-flux::print.first-page-header>
<main style="padding-top: 24px">
    <div style="font-size: 14px; line-height: 20px">
        {{ render_editor_blade(app(SubscriptionSettings::class)->cancellation_text, ['order' => $model]) }}
    </div>
</main>
