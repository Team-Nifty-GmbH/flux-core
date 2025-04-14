<tbody class="bg-uneven">
    <tr>
        @section('order-position')
        <td class="pos py-2 pr-8 align-top">
            @section('order-position.slug-position')
            {{ $position->total_net_price ? $position->slug_position : '' }}
            @show
        </td>
        <td
            class="py-2 pr-8 align-top"
            style="padding-left: {{ $position->depth * 15 }}px"
        >
            @section('order-position.alternative-badge')
            @if ($position->is_alternative)
                <x-badge
                    color="amber"
                    class="mb-2"
                    :text="__('Alternative')"
                    position="right"
                />
            @endif

            @show

            @section('order-position.name')
            <p class="font-italic text-xs">
                {{ $position->product_number }}
            </p>
            @show
            @section('order-position.name')
            <p class="font-semibold">
                {{ $position->name }}
            </p>
            @show
            @section('order-position.description')
            <div class="prose-xs prose">
                {!! $position->description !!}
            </div>
            @show
        </td>
        <td class="py-2 pr-8 text-center align-top">
            @section('order-postion.amount')
            {{ format_number($position->amount) }}
            {{ data_get($position, 'product.unit.abbreviation') }}
            @show
        </td>
        <td class="py-2 text-right align-top">
            @section('order-position.discount')
            @if ($position->total_base_net_price > $position->total_net_price)
                <div class="whitespace-nowrap text-xs">
                    <div class="line-through">
                        {{ $formatter->formatCurrency($isNet ? $position->total_base_net_price : $position->total_base_gross_price, $currency) }}
                    </div>
                    <div>
                        -{{ format_number(diff_percentage($position->total_base_net_price, $position->total_net_price), NumberFormatter::PERCENT) }}
                    </div>
                </div>
            @endif

            @show
            @section('order-position.total-price')
            {{ $position->total_net_price ? $formatter->formatCurrency($isNet ? $position->total_net_price : $position->total_gross_price, $currency) : null }}
            @show
        </td>
        @show
    </tr>
</tbody>
