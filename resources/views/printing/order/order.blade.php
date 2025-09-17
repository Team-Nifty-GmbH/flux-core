@use(\Illuminate\Support\Number)
@use(\FluxErp\Models\PriceList)
@use(\FluxErp\Models\PrintLayout)
@use(\FluxErp\Models\Currency)
@use(\Illuminate\Support\Fluent)
@php
    $isNet = ($model->priceList ?? resolve_static(PriceList::class, 'default'))->is_net;
    $layout = resolve_static(PrintLayout::class,'query')
        ->where('client_id', $model->client_id)
        ->where('model_type', morph_alias($model::class))
        ->first()?->toArray();
@endphp

@section('first-page-header')
<x-flux::print.first-page-header
    :client="$client"
    :address="Fluent::make($model->address_invoice)"
    :$model
    :first-page-header-layout="is_null($layout) ? null : $layout['first_page_header']"
>
    <x-slot:right-block>
        <x-flux::print.elements.first-page-header-right-block-order :model="$model" />
    </x-slot>
</x-flux::print.first-page-header>
@show
<main>
    @section('header')
    <div class="prose-xs pb-4 pt-10">
        {!! Blade::render(html_entity_decode($model->header ?? ''), ['model' => $model]) !!}
    </div>
    @show
    <div class="pb-6">
        @section('positions')
        <table class="w-full table-auto text-xs">
            <thead class="border-b-2 border-black">
                @section('positions.header')
                <tr class="py-2">
                    <th class="py-2 pr-8 text-left font-normal">
                        {{ __('Pos.') }}
                    </th>
                    <th class="py-2 pr-8 text-left font-normal">
                        {{ __('Name') }}
                    </th>
                    <th class="py-2 pr-8 text-center font-normal">
                        {{ __('Amount') }}
                    </th>
                    <th class="py-2 text-right font-normal uppercase">
                        {{ __('Sum') }}
                    </th>
                </tr>
                @show
            </thead>
            @section('positions.positions')
            @foreach ($model->orderPositions as $position)
                <x-flux::print.order.order-position
                    :position="$position"
                    :is-net="$isNet"
                />
            @endforeach

            @show
        </table>
        @show
    </div>
    @if ($summary)
        @section('summary')
        <div class="pb-6">
            <table class="w-full text-xs">
                <tbody class="break-inside-avoid">
                    <tr>
                        <td
                            colspan="3"
                            class="border-b border-black font-semibold"
                        >
                            {{ __('Summary') }}
                        </td>
                    </tr>
                    @foreach ($summary as $summaryItem)
                        <tr>
                            <td>
                                {{ $summaryItem->slug_position }}
                            </td>
                            <td class="whitespace-nowrap">
                                {{ $summaryItem->name }}
                            </td>
                            <td class="float-right text-right">
                                {{ Number::currency($summaryItem->total_net_price ?? 0) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @show
    @endif

    @section('total')
    <table class="w-full pb-16 text-xs">
        <tbody class="break-inside-avoid">
            <tr>
                <td colspan="2" class="border-b-2 border-black font-semibold">
                    {{ __('Total') }}
                </td>
            </tr>
            @section('total.discounts')
            @if ($model->discounts->isNotEmpty())
                <tr>
                    <td class="text-right">
                        {{ __('Sum net without discount') }}
                    </td>
                    <td class="w-0 whitespace-nowrap pl-12 text-right">
                        {{ Number::currency($model->total_base_net_price) }}
                    </td>
                </tr>
                @foreach ($model->discounts as $discount)
                    <tr>
                        <td class="text-right">
                            <span>{{ data_get($discount, 'name') }}</span>
                            <span>
                                {{ Number::percentage(bcmul(data_get($discount, 'discount_percentage', 0), 100)) }}
                            </span>
                        </td>
                        <td class="w-0 whitespace-nowrap pl-12 text-right">
                            {{ Number::currency(bcmul(data_get($discount, 'discount_flat', 0), -1)) }}
                        </td>
                    </tr>
                @endforeach

                <tr class="border-b"></tr>
            @endif

            @show
            @section('total.net')
            <tr>
                <td class="text-right">
                    {{ __('Sum net') }}
                </td>
                <td class="w-0 whitespace-nowrap pl-12 text-right">
                    {{ Number::currency($model->total_net_price) }}
                </td>
            </tr>
            @show
            @section('total.vats')
            @foreach ($model->total_vats ?? [] as $vat)
                <tr>
                    <td class="text-right">
                        {{
                            __('Plus :percentage VAT from :total_net', [
                                'percentage' => Number::percentage(bcmul($vat['vat_rate_percentage'], 100)),
                                'total_net' => Number::currency($vat['total_net_price']),
                            ])
                        }}
                    </td>
                    <td class="w-0 whitespace-nowrap pl-12 text-right">
                        {{ Number::currency($vat['total_vat_price']) }}
                    </td>
                </tr>
            @endforeach

            @show
            @section('total.gross')
            <tr class="font-bold">
                <td class="text-right">
                    {{ __('Total Gross') }}
                </td>
                <td class="w-0 whitespace-nowrap pl-12 text-right">
                    {{ Number::currency($model->total_gross_price) }}
                </td>
            </tr>
            @show
        </tbody>
    </table>
    @show
    @section('footer')
    <div class="prose-xs break-inside-avoid">
        {!! Blade::render(html_entity_decode($model->footer ?? ''), ['model' => $model]) !!}
        {!!
            $model
                ->vatRates()
                ->distinct()
                ->pluck('footer_text')
                ->filter()
                ->map(fn (string $text) => Blade::render(html_entity_decode($text), ['model' => $model]))
                ->implode('<br>')
        !!}
    </div>
    @show
</main>
