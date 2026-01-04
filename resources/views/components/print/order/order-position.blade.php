@use(Illuminate\Support\Facades\Blade; use Illuminate\Support\Number)

<tbody class="bg-uneven">
    <tr>
        @section('position-identifier.wrapper')
        <td class="pos py-2 pr-8 align-top">
            @section('position-identifier.content')
            {{ $position->total_net_price ? $position->slug_position : '' }}
            @show
        </td>
        @show

        @section('position-name.wrapper')
        <td
            class="py-2 pr-8 align-top"
            style="padding-left: {{ $position->depth * 15 }}px"
        >
            @section('position-name.content')
            @section('$position-name.content.alternative')
            @if ($position->is_alternative)
                <x-badge
                    color="amber"
                    class="mb-2"
                    :text="__('Alternative')"
                    position="right"
                />
            @endif

            @show
            @section('$position-name.content.product-number')
            <p class="font-italic text-xs">
                {{ $position->product_number }}
            </p>
            @show
            @section('$position-name.content.name')
            <p class="font-semibold">
                {{ render_editor_blade($position->name, ['position' => $position]) }}
            </p>
            @show
            @section('$position-name.content.description')
            <div class="prose-xs">
                {{ render_editor_blade($position->description, ['position' => $position]) }}
            </div>
            @show
            @show
        </td>
        @show

        @section('position-quantity.wrapper')
        <td class="py-2 pr-8 text-center align-top">
            @section('position-quantity.content')
            @if (! $position->is_free_text && ! $position->is_bundle_position)
                {{ Number::format($position->amount) }}
                {{ data_get($position, 'product.unit.abbreviation') }}
            @endif

            @show
        </td>
        @show

        @section('position-price.wrapper')
        <td class="py-2 text-right align-top">
            @section('position-price.content')
            @if (bccomp($position->total_base_net_price ?? 0, $position->total_net_price ?? 0, 2) === 1)
                <div class="whitespace-nowrap text-xs">
                    <div class="line-through">
                        {{ Number::currency($isNet ? $position->total_base_net_price : $position->total_base_gross_price) }}
                    </div>
                    <div>
                        -{{ Number::percentage(bcmul(diff_percentage($position->total_base_net_price, $position->total_net_price), 100), maxPrecision: 2) }}
                    </div>
                </div>
            @endif

            {{ $position->total_net_price ? Number::currency($isNet ? $position->total_net_price : $position->total_gross_price) : null }}
            @show
        </td>
        @show
    </tr>
</tbody>
