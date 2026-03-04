@use(\Illuminate\Support\Fluent)
@use(\FluxErp\Settings\SubscriptionSettings)

<x-flux::print.first-page-header
    :address="Fluent::make($model->address_invoice)"
    :$model
>
    <x-slot:right-block>
        <table class="border-separate border-spacing-x-2">
            <tbody class="align-text-top text-xs leading-none">
                <tr class="leading-none">
                    <td class="py-0 text-left font-semibold">
                        {{ __('Order no.') }}
                    </td>
                    <td class="py-0 text-right">
                        {{ $model->order_number }}
                    </td>
                </tr>
                <tr class="leading-none">
                    <td class="py-0 text-left font-semibold">
                        {{ __('Customer no.') }}
                    </td>
                    <td class="py-0 text-right">
                        {{ $model->contact()->withTrashed()->value('customer_number') }}
                    </td>
                </tr>
                <tr class="leading-none">
                    <td class="py-0 text-left font-semibold">
                        {{ __('Date') }}
                    </td>
                    <td class="py-0 text-right">
                        {{ now()->locale(app()->getLocale())->isoFormat('L') }}
                    </td>
                </tr>
            </tbody>
        </table>
    </x-slot>
</x-flux::print.first-page-header>
<main class="pt-6">
    <div class="prose-sm">
        {{ render_editor_blade(app(SubscriptionSettings::class)->cancellation_text, ['order' => $model]) }}
    </div>
</main>
