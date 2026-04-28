@use(Illuminate\Support\Facades\Blade; use Illuminate\Support\Number)
<tbody>
    <tr
        @if($loop ?? false)
            @if($loop->odd)
                style="background: #f2f4f7"
            @endif
        @endif
    >
        <td
            class="pos"
            style="
                padding-top: 8px;
                padding-bottom: 8px;
                padding-right: 32px;
                vertical-align: top;
            "
        >
            {{ $position->total_net_price ? $position->slug_position : '' }}
        </td>
        <td
            style="padding-top: 8px; padding-bottom: 8px; padding-right: 32px; vertical-align: top; padding-left: {{ $position->depth * 15 }}px;"
        >
            @if($position->is_alternative)
                <x-badge
                    color="amber"
                    style="margin-bottom: 8px"
                    :text="__('Alternative')"
                    position="right"
                />
            @endif

            <p
                style="
                    font-style: italic;
                    font-size: 12px;
                    margin: 0;
                    padding: 0;
                "
            >{{ $position->product_number }}</p>
            <p style="font-weight: 600; margin: 0; padding: 0">
                {{ render_editor_blade($position->name, ['position' => $position]) }}
            </p>
            <div style="font-size: 12px; line-height: 16px">
                {{ render_editor_blade($position->description, ['position' => $position]) }}
            </div>
        </td>
        <td
            style="
                padding-top: 8px;
                padding-bottom: 8px;
                padding-right: 32px;
                text-align: center;
                vertical-align: top;
            "
        >
            @if(! $position->is_free_text && ! $position->is_bundle_position)
                {{ Number::format($position->amount) }}
                {{ data_get($position, 'product.unit.abbreviation') }}
            @endif
        </td>
        <td
            style="
                padding-top: 8px;
                padding-bottom: 8px;
                text-align: right;
                vertical-align: top;
            "
        >
            @if(bccomp($position->total_base_net_price ?? 0, $position->total_net_price ?? 0, 2) === 1)
                <div style="font-size: 12px; white-space: nowrap">
                    <div style="text-decoration: line-through">
                        {{ Number::currency($isNet ? $position->total_base_net_price : $position->total_base_gross_price) }}
                    </div>
                    <div>
                        -{{ Number::percentage(bcmul(diff_percentage($position->total_base_net_price, $position->total_net_price), 100), maxPrecision: 2) }}
                    </div>
                </div>
            @endif

            {{ $position->total_net_price ? Number::currency($isNet ? $position->total_net_price : $position->total_gross_price) : null }}
        </td>
    </tr>
</tbody>
