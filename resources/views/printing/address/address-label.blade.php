<table class="w-full text-2xs" style="page-break-inside: avoid">
    <tr colspan="100%" class="text-3xs">
        <td>
            {{ $client->postal_address_one_line }}
        </td>
    </tr>
    <tr>
        <td>
            <div style="line-height: 0.6rem">
                {!! implode('<br />', $model->postal_address) !!}
            </div>
        </td>
        <td style="text-align: right; vertical-align: top;">
            <div class="text-right max-h-72 inline-block float-right">
                <img class="logo-small" src="{{ $client->logo_small }}"  alt="logo-small"/>
            </div>
        </td>
    </tr>
</table>
