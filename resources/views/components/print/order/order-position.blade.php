<tbody class="bg-uneven">
<tr>
    <td class="pos py-4 pr-8 align-top">
        {{ $position->total_net_price ? $position->slug_position : '' }}
    </td>
    <td class="py-4 pr-8 align-top" style="padding-left: {{ $position->depth * 15 }}px">
        @if($position->is_alternative)
            <x-badge color="warning" class="mb-2">
                {{ __('Alternative') }}
            </x-badge>
        @endif
        <p class="font-italic text-xs">
            {{ $position->product_number }}
        </p>
        <p class="font-semibold">
            {{ $position->name }}
        </p>
        <div class="prose prose-xs">
            {!! $position->description !!}
        </div>
    </td>
    <td class="py-4 pr-8 text-center align-top">
        {{ format_number($position->amount) }} {{ data_get($position, 'product.unit.abbreviation') }}
    </td>
    <td class="py-4 text-right align-top">
        @if($position->total_base_net_price > $position->total_net_price)
            <div class="text-xs whitespace-nowrap">
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
