@use(Illuminate\Support\Facades\Blade; use Illuminate\Support\Number)
@php
    $descriptionChunks = split_html_for_print(
        render_editor_blade($position->description, ['position' => $position])->toHtml()
    );
    $rowBackground = ($loop ?? false) && $loop->odd ? 'background: #f2f4f7;' : '';
    $rowBottomPadding = $descriptionChunks === [] ? 'padding-bottom: 8px;' : '';
@endphp
<tbody>
    <tr
        @if ($rowBackground)
            style="{{ $rowBackground }}"
        @endif
    >
        <td
            class="pos"
            style="
                padding-top: 8px;
                padding-right: 32px;
                vertical-align: top;
                {{ $rowBottomPadding }}
            "
        >
            {{ $position->total_net_price ? $position->slug_position : '' }}
        </td>
        <td
            style="padding-top: 8px; padding-right: 32px; vertical-align: top; {{ $rowBottomPadding }} padding-left: {{ $position->depth * 15 }}px;"
        >
            @if ($position->is_alternative)
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
        </td>
        <td
            style="
                padding-top: 8px;
                padding-right: 32px;
                text-align: center;
                vertical-align: top;
                {{ $rowBottomPadding }}
            "
        >
            @if (! $position->is_free_text && ! $position->is_bundle_position)
                {{ Number::format($position->amount) }}
                {{ data_get($position, 'product.unit.abbreviation') }}
            @endif
        </td>
        <td
            style="
                padding-top: 8px;
                text-align: right;
                vertical-align: top;
                {{ $rowBottomPadding }}
            "
        >
            @if (bccomp($position->total_base_net_price ?? 0, $position->total_net_price ?? 0, 2) === 1)
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
    @foreach ($descriptionChunks as $chunk)
        <tr
            @if ($rowBackground)
                style="{{ $rowBackground }}"
            @endif
        >
            <td class="pos"></td>
            <td
                style="font-size: 12px; line-height: 16px; vertical-align: top; padding-right: 32px; {{ $loop->last ? 'padding-bottom: 8px;' : '' }} padding-left: {{ $position->depth * 15 }}px;"
            >
                {!! $chunk !!}
            </td>
            <td></td>
            <td></td>
        </tr>
    @endforeach
</tbody>
