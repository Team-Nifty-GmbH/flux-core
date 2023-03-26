<table class="content-block">
    <thead>
    <tr class="theadrow">
        <th class="tc-1">Anzahl</th>
        <th class="tc-2">Artikelnr. Extern</th>
        <th class="tc-3 th-single">Artikelnr. Intern</th>
        <th class="tc-4 th-single">Artikelbeschreibung</th>
        <th class="tc-5 th-single text-center">Rennergy EAN</th>
    </tr>
    </thead>
    <tbody>
    @foreach($auftrag[0]['POSITIONEN'] as $position)
        @if($position['POSITION_ARTIKELNUMMER'] && !$position['POSITION_FLAGBUNDLEBESTANDTEIL'])
            <tr>
                <td class="tc-1">
                    {{ $position['POSITION_ANZAHL'] ?? '' }}
                </td>

                <td class="tc-2">
                </td>
                <td class="tc-3 th-single">
                    {{ $position['POSITION_ARTIKELNUMMER'] ?? '' }}
                </td>
                <td class="tc-4 th-single">
                    {!! $position['POSITION_BEZEICHNUNG'] !!}
                </td>
                <td class="tc-5 td-number th-single text-center">
                </td>
            </tr>
        @endif
    @endforeach
    </tbody>
</table>
