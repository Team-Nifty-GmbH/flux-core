<style>
    .text-center {
        text-align: center;
    }
</style>
<table class="content-block">
    <thead>
    <tr class="theadrow">
        <th class="tc-2">Anzahl</th>
        <th class="tc-3">Beschreibung</th>
        <th class="th-single text-center">Anzahl</th>
        <th class="th-single text-center">Umtausch</th>
        <th class="th-single text-center">Rücksendung</th>
        <th class="th-single text-center">Defekt</th>
    </tr>
    </thead>
    <tbody>
    @foreach($auftrag[0]['POSITIONEN'] as $position)

        @if($position['POSITION_ARTIKELNUMMER'] && !$position['POSITION_FLAGBUNDLEBESTANDTEIL'])
        <tr>
            <td class="tc-2">
                    {{ $position['POSITION_ANZAHL'] ?? '' }}
            </td>

            <td class="tc-3"><div>
                    <div>{{ $position['POSITION_ARTIKELNUMMER'] ?? '' }}</div>
                    <div style="font-weight: bold;">
                        {!! $position['POSITION_BEZEICHNUNG'] !!}</div>
                </div>
            </td>
            <td class="td-number th-single text-center">
                _______
            </td>
            <td class="td-number th-single text-center">
                <input class="umtausch" type="checkbox" />
            </td>
            <td class="td-number th-single text-center">
                <input class="umtausch" type="checkbox" />
            </td>
            <td class="td-number th-single text-center">
                <input class="umtausch" type="checkbox" />
            </td>

        </tr>
        @endif
    @endforeach
    </tbody>
</table>
