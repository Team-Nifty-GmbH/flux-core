<table class="w-full text-2xs" style="page-break-inside: avoid">
    <tr colspan="100%" class="text-3xs">
        <td>
            @section('client_address')
                {{ $client->postal_address_one_line }}
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
        <td style="text-align: right; vertical-align: top;">
            @section('logo')
                <div class="text-right max-h-72 inline-block float-right">
                    <img class="logo-small" src="{{ $client->logo_small }}"  alt="logo-small"/>
                </div>
            @show
        </td>
    </tr>
</table>
