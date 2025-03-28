<tbody class="bg-uneven">
    <tr>
        <td class="pos py-2 pr-8 align-top">
            {{ $position->total_net_price ? $position->slug_position : '' }}
        </td>
        <td
            class="py-2 pr-8 align-top"
            style="padding-left: {{ ($position->depth - 1) * 15 }}px"
        >
            @if ($position->is_alternative)
                <x-badge
                    color="amber"
                    class="mb-2"
                    :text="__('Alternative')"
                    position="right"
                />
            @endif

            <div>
                @if ($position->depth > 0)
                    <div class="float-left w-4">↳</div>
                @endif

                <div class="float-left">
                    <p class="font-italic text-xs">
                        {{ $position->product_number }}
                    </p>
                    <p class="font-semibold">
                        {{ $position->name }}
                    </p>
                    <div class="prose-xs prose">
                        {!! $position->description !!}
                    </div>
                </div>
            </div>
        </td>
        <td class="py-2 pr-8 text-center align-top">
            {{ format_number($position->amount) }}
            {{ data_get($position, 'product.unit.abbreviation') }}
        </td>
        <td class="py-2 text-right align-top">
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

            {{ $position->total_net_price ? $formatter->formatCurrency($isNet ? $position->total_net_price : $position->total_gross_price, $currency) : null }}
        </td>
    </tr>
</tbody>
