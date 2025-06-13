@use(Illuminate\Support\Number)
<tbody class="bg-uneven">
    <tr>
        <td class="pos py-2 pr-8 align-top">
            {{ $position->total_net_price ? $position->slug_position : '' }}
        </td>
        <td
            class="py-2 pr-8 align-top"
            style="padding-left: {{ $position->depth * 15 }}px"
        >
            @if ($position->is_alternative)
                <x-badge
                    color="amber"
                    class="mb-2"
                    :text="__('Alternative')"
                    position="right"
                />
            @endif

            <p class="font-italic text-xs">
                {{ $position->product_number }}
            </p>
            <p class="font-semibold">
                {{ $position->name }}
            </p>
            <div class="prose-xs">
                {!! $position->description !!}
            </div>
        </td>
        <td class="py-2 pr-8 text-center align-top">
            @if (! $position->is_free_text && ! $position->is_bundle_position)
                {{ Number::format($position->amount) }}
                {{ data_get($position, 'product.unit.abbreviation') }}
            @endif
        </td>
        <td class="py-2 text-right align-top">
            @if ($position->total_base_net_price > $position->total_net_price)
                <div class="text-xs whitespace-nowrap">
                    <div class="line-through">
                        {{ Number::currency($isNet ? $position->total_base_net_price : $position->total_base_gross_price) }}
                    </div>
                    <div>
                        -{{ Number::percentage(bcmul(diff_percentage($position->total_base_net_price, $position->total_net_price), 100)) }}
                    </div>
                </div>
            @endif

            {{ $position->total_net_price ? Number::currency($isNet ? $position->total_net_price : $position->total_gross_price) : null }}
        </td>
    </tr>
</tbody>
