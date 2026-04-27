<table style="font-size: 10px; width: 100%; page-break-inside: avoid">
    <tr colspan="100%" style="font-size: 8px">
        <td>
            @section('tenant_address')
                {{ $tenant->postal_address_one_line }}
            @show
        </td>
    </tr>
    <tr>
        <td>
            @section('address')
                <div style="line-height: 0.6rem">
                    {!! implode('<br />', $model->postal_address) !!}
                </div>
            @show
        </td>
        <td style="text-align: right; vertical-align: top">
            @section('logo')
                <div
                    style="
                        float: right;
                        display: inline-block;
                        max-height: 288px;
                        text-align: right;
                    "
                >
                    <img
                        class="logo-small"
                        src="{{ $tenant->logo_small }}"
                        alt="logo-small"
                    />
                </div>
            @show
        </td>
    </tr>
</table>
