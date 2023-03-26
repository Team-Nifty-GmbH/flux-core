<table class="content-block">
    <thead>
    <tr class="theadrow">
        <th class="tc-1">Pos.</th>
        <th class="tc-2">Anzahl</th>
        <th class="tc-3">Beschreibung</th>
        <th class="th-single td-number">Einzelpreis</th>
        <th class="th-single td-number">Gesamtpreis</th>
    </tr>
    </thead>
    <tbody>
    @foreach($auftrag[0]['POSITIONEN'] as $position)
        @if($position['POSITION_BEZEICHNUNG'] == 'Zusammenfassung')
            <tr>
                <td colspan="5">
                    <div class="totalsum" style="font-weight: bold;">
                        Zusammenfassung
                    </div>
                </td>
            </tr>
        @else
        <tr class="trow"
        @if(((!$position['POSITION_FLAGBUNDLEBESTANDTEIL'] && !$position['POSITION_FLAGFREIZEILE']) || $position['POSITION_FLAGBLOCK']) && !$position['POSITION_FLAGZUSAMMENFASSUNG'])
           style="border-top: 1px solid;"
            @endif
        >
            <td class="tc-1" style="
            @if($position['POSITION_BLOCKANFANG'] && $loop->index > 0 && !$position['POSITION_FLAGZUSAMMENFASSUNG'])
                padding-top: 20px;
            @endif
                ">
                @if((!$position['POSITION_FLAGBUNDLEBESTANDTEIL'] && !$position['POSITION_FLAGFREIZEILE']) || $position['POSITION_FLAGBLOCK'])
                    {{ $position['POSITION_POSITIONSNUMMER'] ?? '' }}
                @endif
            </td>
            <td class="tc-2">
                @if(!$position['POSITION_FLAGFREIZEILE'])
                    {{ $position['POSITION_ANZAHL'] ?? '' }}
                @endif
            </td>
            <td class="tc-3"><div
                    @if($position['POSITION_FLAGBUNDLEBESTANDTEIL'])
                    style="padding-left:30px;"
                    @endif
                ><div>{{ $position['POSITION_ARTIKELNUMMER'] ?? '' }}</div>
                    @if(($position['POSITION_FLAGALTERNATIV'] && !$position['POSITION_FLAGFREIZEILE']) ||
                        ($position['POSITION_FLAGZUSAMMENFASSUNG'] && $position['POSITION_BEDARF'] ||
                        ($position['POSITION_BUNDLEID'] > 0)
                        ))

                        <div style="font-style: italic;">
                            <div>{{ $position['POSITION_BEDARF'] }}</div>
                            @if($position['POSITION_BUNDLEID'] > 0 && $position['POSITION_FK_BUNDLEARTIKEL_ID'] > 0)
                                <div>Bestandteil von {!! $posCollection->where('POSITION_BUNDLEARTIKEL_ID',$position['POSITION_FK_BUNDLEARTIKEL_ID'])->first()['POSITION_BEZEICHNUNG'] ?? $position['POSITION_BUNDLENAME']!!}</div>
                            @endif
                        </div>
                    @endif
                    <div style="
                    @if(!$position['POSITION_FLAGFREIZEILE'] || $position['POSITION_FLAGBLOCK'])
                        font-weight: bold;
                    @if($position['POSITION_FLAGBLOCK'])
                        margin-left: -40px;
                    @endif
                    @endif
                    @if($position['POSITION_FLAGZUSAMMENFASSUNG'] && !$position['POSITION_BLOCKNUMMER'])
                        padding-top: 20px; margin-left: -40px; font-style: italic; font-weight: bold;
                    @endif
                    @if($position['POSITION_BLOCKANFANG'] && $loop->index > 0)
                        padding-top: 20px;
                    @endif
                        ">
                        {!! $position['POSITION_BEZEICHNUNG'] !!}</div>
                </div>
            </td>
            <td class="td-number th-single tc-4">
                @if(!$position['POSITION_FLAGBUNDLEBESTANDTEIL'] && !$position['POSITION_FLAGFREIZEILE'])
                    <div>{{ number_format($position['POSITION_VKB'], 2, ',', '.') . ' €' ?? '' }}</div>
                    <div>
                        @if($position['POSITION_RABATTVKBENDPREIS_NUMBER'] > 0)
                            {{ $position['POSITION_RABATTVKBENDPREIS'] . ' %' ?? '' }}
                        @else
                            &nbsp;
                        @endif
                    </div>
                @endif
            </td>

            <td class="td-number th-single tc-5">
                @if($position['POSITION_BLOCKENDE'])
                    {{ number_format($blocksums[$position['POSITION_BLOCKNUMMER']]['sum'], 2, ',', '.') }} €
                @elseif(!$position['POSITION_FLAGALTERNATIV'])
                    @if(!$position['POSITION_FLAGBUNDLEBESTANDTEIL'] && !$position['POSITION_FLAGFREIZEILE'])
                        {{ $position['POSITION_VKNETTOANZAHL_MITRABATT2'] . ' €' ?? '' }}
                    @elseif($position['POSITION_FLAGBLOCK'] && (int)$position['POSITION_VKNETTOANZAHL_MITRABATT2'] > 0)
                        {{ $position['POSITION_VKNETTOANZAHL_MITRABATT2'] . ' €' ?? '' }}
                    @elseif($position['POSITION_FLAGZUSAMMENFASSUNG'] && $position['POSITION_BLOCKNUMMER'] && $activeBlock[$position['POSITION_BLOCKNUMMER']])
                        {{ number_format($blocksums[$position['POSITION_BLOCKNUMMER']]['sum'], 2, ',', '.') }} €
                    @endif
                @elseif($position['POSITION_FLAGBLOCK'] || $position['POSITION_FLAGBUNDLEBESTANDTEIL'])
                @else
                    0,00
                @endif
            </td>

        </tr>
        @endif
        @php
            $activeBlock[$position['POSITION_BLOCKNUMMER']] = true;
        @endphp
    @endforeach
    <tr class="totalsumrow" style="line-height: 18px; padding-bottom: 5px; border-top: 1px solid;">
        <td colspan="4" style="text-align: left; padding: 20px 0 0;">
            @if($auftrag[0]['A_ENDBETRAGBRUTTO'] == $auftrag[0]['AUFTRAG_ENDBETRAGNETTO'])
                <div class="row"><div style="padding-left: 10px">Summe Brutto</div></div>
                <div class="row"><div style="padding-left: 10px">Inklusive 19%</div></div>
            @else
                <div class="row"><div style="padding-left: 10px">Summe Netto</div></div>
                <div class="row"><div style="padding-left: 10px">Zuzüglich 19%</div></div>
            @endif
            <div class="row"><div class="totalsum"  style="font-weight: bold;">Gesamtbetrag</div></div>
        </td>
        <td style="text-align: right; padding: 20px 0 0;" class="td-number">
            <div class="row"><div style="padding-right: 10px">{{ $auftrag[0]['AUFTRAG_ENDBETRAGNETTO'] }} €</div></div>
            <div class="row"><div style="padding-right: 10px">{{ $auftrag[0]['A_MWSTVALUENUM'] }} €</div></div>
            <div class="row"><div class="totalsum"  style="font-weight: bold; text-align: right;">{{ $auftrag[0]['A_ENDBETRAGBRUTTO'] }} €</div></div>
        </td>
    </tr>
    </tbody>
</table>
<div style="padding-left:0">
    {!! nl2br($auftrag[0]['AUFTRAG_ZAHLUNGSHINWEIS'], true) !!}
    <br />
    {!! nl2br($auftrag[0]['AUFTRAG_FUSSTEXT'], true) !!}
</div>
