
@php
    $positionen = $AUFTRÄGE[0]['POSITIONEN'];
    $activeBlock = 0;
    $subCounter = 0;
    $blockActive = false;
    foreach ($positionen as $key => $position) {
        if($position['POSITION_FLAGBLOCK'] && $blockActive == false) {
            // Block Anfang
            $activeBlock++;
            $positionen[$key]['POSITION_BLOCKNUMMER'] = $activeBlock;
            $positionen[$key]['POSITION_BLOCKANFANG'] = true;
            $positionen[$key]['POSITION_POSITIONSNUMMER'] = str_pad($activeBlock, 2, "0", STR_PAD_LEFT);
            $blockActive = true;
            $subCounter = 0;
        } elseif ($position['POSITION_FLAGBLOCK'] && $blockActive == true) {
            // Block Ende
            $positionen[$key]['POSITION_BLOCKNUMMER'] = $activeBlock;
            $positionen[$key]['POSITION_BLOCKENDE'] = true;
            $positionen[$key]['POSITION_POSITIONSNUMMER'] = str_pad($activeBlock, 2, "0", STR_PAD_LEFT);
            $blockActive = false;
        } elseif ($blockActive){
            // Block Position
            if(!$position['POSITION_FLAGFREIZEILE'] && !$position['POSITION_FLAGBUNDLEBESTANDTEIL']) {
                $subCounter++;
            }
            $positionen[$key]['POSITION_BLOCKNUMMER'] = $activeBlock;
            $positionen[$key]['POSITION_POSITIONSNUMMER'] = str_pad($activeBlock, 2, "0", STR_PAD_LEFT) . '.' . str_pad($subCounter, 2, "0", STR_PAD_LEFT);
        } else {
            // Normale Position
            $activeBlock++;
            $positionen[$key]['POSITION_BLOCKNUMMER'] = $activeBlock;
            $positionen[$key]['POSITION_POSITIONSNUMMER'] = str_pad($activeBlock, 2, "0", STR_PAD_LEFT);
        }
    }
    $AUFTRÄGE[0]['POSITIONEN'] = $positionen;
    $posCollection = new \Illuminate\Support\Collection($AUFTRÄGE[0]['POSITIONEN']);
    $blocks = array_keys($posCollection->where('POSITION_FLAGBLOCK', 1)->groupBy('POSITION_BLOCKNUMMER')->toArray());
    $blocksums = [];
$posattributes = ["POSITION_ANZAHL"=>	"0",
					"POSITION_ARTIKELNUMMER"=>	"",
					"POSITION_BEDARF"=>	"",
					"POSITION_FLAGALTERNATIV"=>	"",
					"POSITION_BEZEICHNUNG"=>	"Zusammenfassung",
					"POSITION_FLAGBLOCK"=>	"0",
					"POSITION_FLAGBUNDLEBESTANDTEIL"=>	"0",
					"POSITION_FLAGFREIZEILE"=>	"1",
					"POSITION_POSITIONSNUMMER"=>	"00",
					"POSITION_RABATTVKBENDPREIS"=>	"0,00",
					"POSITION_VKNETTOANZAHL_MITRABATT2"=>	"0,00",
					"POSITION_VKNETTOSTÜCK"=>	"0,00",
					"POSITION_BLOCKNUMMER"=>	"",
					"POSITION_FLAGZUSAMMENFASSUNG"=>	"1",
					"POSITION_RABATTVKBENDPREIS_NUMBER"=>	0,
					'POSITION_UUID' => null,
					"POSITION_VKNETTOANZAHL_MITRABATT2_NUMBER"=>	0,
					'POSITION_BLOCKANFANG' => false,
					'POSITION_BLOCKENDE' => false,
					'POSITION_BUNDLEID' => 0,
					'POSITION_BUNDLENAME' => '',
					'POSITION_VKB' => 0
					];
    $zusammenfassung = $posattributes;
    $zusammenfassung['POSITION_FLAGZUSAMMENFASSUNG'] = '1';
    $zusammenfassung['POSITION_BEZEICHNUNG'] = 'Zusammenfassung';

    $totalNet = $posCollection
            ->where('POSITION_FLAGBLOCK', '!=', "1")
            ->where('POSITION_FLAGALTERNATIV', '!=', "1")
            ->sum('POSITION_VKNETTOANZAHL_MITRABATT2_NUMBER');

    if(count($blocks)) {
        array_push($AUFTRÄGE[0]['POSITIONEN'], $zusammenfassung);
    }
    foreach ($blocks as $key => $block) {
        $blockStart = $posCollection->where('POSITION_BLOCKNUMMER', $block)->where('POSITION_BLOCKANFANG', true)->first();
        $blockEnd = $posCollection->where('POSITION_BLOCKNUMMER', $block)->where('POSITION_BLOCKANFANG', true)->last();
        if($blockStart['POSITION_FLAGALTERNATIV'] != 1) {
            $res = $posCollection
            ->where('POSITION_BLOCKNUMMER', $block)
            ->where('POSITION_FLAGBLOCK', '!=', 1)
            ->where('POSITION_FLAGALTERNATIV', '!=', 1)
            ->sum('POSITION_VKNETTOANZAHL_MITRABATT2_NUMBER');
        } else {
            $res = $posCollection->where('POSITION_BLOCKNUMMER', $block)->where('POSITION_FLAGBLOCK', '!=', 1)->sum('POSITION_VKNETTOANZAHL_MITRABATT2_NUMBER');
        }
        $blocks[$key] = $res;
        $blocksums[$block]['sum'] = $res;
        $blocksums[$block]['uuid_start'] = $blockStart['POSITION_UUID'];
        $blocksums[$block]['uuid_end'] = $blockEnd['POSITION_UUID'];
        $blockObject = $posattributes;
        $blockObject['POSITION_BEZEICHNUNG'] = $blockStart['POSITION_BEZEICHNUNG'];
        $blockObject['POSITION_VKNETTOANZAHL_MITRABATT2'] =  number_format($res, 2, ',', '.');
        $blockObject['POSITION_FLAGBLOCK'] =  1;
        $blockObject['POSITION_POSITIONSNUMMER'] =  $blockStart['POSITION_POSITIONSNUMMER'];
        $blockObject['POSITION_BLOCKNUMMER'] =  $blockStart['POSITION_BLOCKNUMMER'];
        $blockObject['POSITION_BEDARF'] =  $blockStart['POSITION_BEDARF'];
        array_push($AUFTRÄGE[0]['POSITIONEN'], $blockObject);
    }

    $activeBlock = [];
@endphp

<x-slot name="header">
    <div class="following-pages-header">
        <div class="theadrow">
            <div class="tc-1">Pos.</div>
            <div class="tc-2">Anzahl</div>
            <div class="tc-3">Beschreibung</div>
            <div class="th-single td-number">Einzelpreis</div>
            <div class="th-single td-number">Gesamtpreis</div>
        </div>
    </div>
</x-slot>
<main>
    <div class="header-client">
        <div class="client">{{ $MANDANT['MANDANT_FIRMENNAME'] }}, {{ $MANDANT['MANDANT_STRASSE'] }}, {{ $MANDANT['MANDANT_PLZ'] }} {{ $MANDANT['MANDANT_ORT'] }}</div>
        <div class="addresses">
            <div class="invoice-address" style="display: inline-block">
                <div>{{ $ADRESSEN['KUNDE_RECHNUNGSADRESSE_FIRMA'] ?? '' }}</div>
                <div>{{ $ADRESSEN['KUNDE_RECHNUNGSADRESSE_VORNAME'] ?? '' }} {{ $ADRESSEN['KUNDE_RECHNUNGSADRESSE_NACHNAME'] ?? '' }}</div>
                <div>{{ $ADRESSEN['KUNDE_RECHNUNGSADRESSE_STRASSE'] ?? '' }}</div>
                <div>{{ $ADRESSEN['KUNDE_RECHNUNGSADRESSE_PLZ'] ?? '' }} {{ $ADRESSEN['KUNDE_RECHNUNGSADRESSE_ORT'] ?? '' }}</div>
            </div>
            <div class="delivery-address">
                <div style="font-weight: bold;">Lieferadresse:</div>
                <div>{{ $ADRESSEN['KUNDE_LIEFERADRESSE_FIRMA'] ?? '' }}</div>
                <div>{{ $ADRESSEN['KUNDE_LIEFERADRESSE_VORNAME'] ?? '' }} {{ $ADRESSEN['KUNDE_LIEFERADRESSE_NACHNAME'] ?? '' }}</div>
                <div>{{ $ADRESSEN['KUNDE_LIEFERADRESSE_STRASSE'] ?? '' }}</div>
                <div>{{ $ADRESSEN['KUNDE_LIEFERADRESSE_PLZ'] ?? '' }} {{ $ADRESSEN['KUNDE_LIEFERADRESSE_ORT'] ?? '' }}</div>
            </div>
        </div>
        <div class="infoblock">
            <div>Mobil: {{ $ADRESSEN['KUNDE_RECHNUNGSADRESSE_MOBIL'] ?? '' }}</div>
            <div>Tel: {{ $ADRESSEN['KUNDE_RECHNUNGSADRESSE_TELEFON'] ?? '' }}</div>
            <div>Email: {{ $ADRESSEN['KUNDE_RECHNUNGSADRESSE_EMAIL'] ?? '' }}</div>
            <div class="subject">
                {{ $AUFTRÄGE[0]['AUFTRAG_BELEGTYP'] ?? '' }} {{ $AUFTRÄGE[0]['AUFTRAG_BELEGNUMMER'] ?? '' }} vom {{ $AUFTRÄGE[0]['AUFTRAG_BELEGDATUM'] ?? '' }}
            </div>
            <div class="infoblock-bottom">
                <div class="infoblock-left">
                    <div class="infoblock-td">
                        <div class="infoblock-label">Kommission: </div>
                        <div class="pl-10">{{ $AUFTRÄGE[0]['AUFTRAG_KOMMISSION'] ?? '' }}</div>
                    </div>
                    <div class="infoblock-td">
                        <div class="infoblock-label">Auftrag-Nr.: </div>
                        <div class="pl-10">{{ $AUFTRÄGE[0]['AUFTRAG_AUFTRAGSNUMMER'] ?? '' }}</div>
                    </div>
                    <div class="infoblock-td">
                        <div class="infoblock-label">Kunden-Nr.: </div>
                        <div class="pl-10">{{ $AUFTRÄGE[0]['AUFTRAG_KUNDENNUMMER'] ?? '' }}</div>
                    </div>
                    <div class="infoblock-td">
                        <div class="infoblock-label">Tel.-Avis: </div>
                        <div class="pl-10">{{ $AUFTRÄGE[0]['LOGISTIK_ANWEISUNGEN_AVISIEREN'] ?? '' }}</div>
                    </div>
                    <div class="infoblock-td">
                        <div class="infoblock-label">Anm.-Logistik: </div>
                        <div class="pl-10">{{ $AUFTRÄGE[0]['LOGISTIK_ANWEISUNGEN_ANMERKUNG_LOGISTIK'] ?? '' }}</div>
                    </div>
                </div>
                <div class="infoblock-right">
                    <div class="infoblock-td">
                        <div class="infoblock-label">Zuständiger Vertreter: </div>
                        <div class="pl-10">{{ $AUFTRÄGE[0]['AUFTRAG_VERTRETER'] ?? '' }}</div>
                    </div>
                    <div class="infoblock-td">
                        <div class="infoblock-label">Sachbearbeiter: </div>
                        <div class="pl-10">{{ $AUFTRÄGE[0]['AUFTRAG_SACHBEARBEITER'] ?? '' }}</div>
                    </div>
                    <div class="infoblock-td">
                        <div class="infoblock-label">Leistungs-/ Lieferdatum: </div>
                        <div class="pl-10">{{ $AUFTRÄGE[0]['LOGISTIK_ANWEISUNGEN_LIEFERDATUM'] ?? '' }}</div>
                    </div>
                </div>
            </div>
            @if($AUFTRÄGE[0]['AUFTRAG_KOPFTEXT'])
                <div class="header-text">
                    {!! nl2br($AUFTRÄGE[0]['AUFTRAG_KOPFTEXT'], true) !!}
                </div>
            @endif
        </div>
    </div>
    <x-dynamic-component :component="'positions_custom.' . ($sublayout ?? 'index')" :blocksums="$blocksums" :pos-collection="$posCollection" :auftrag="$AUFTRÄGE"></x-dynamic-component>

</main>
<script>
    class MyHandler extends Paged.Handler {
        constructor(chunker, polisher, caller) {
            super(chunker, polisher, caller);
        }
        afterRendered(pages) {
            var nodes = document.querySelectorAll('.pagedjs_page');
            var last = nodes[nodes.length- 1];
            console.log(last);
            if (!last.querySelector('main').querySelector('table')) {
                last.querySelector('.theadrow').style.display = 'none'
            }
        }
    }
    Paged.registerHandlers(MyHandler);
</script>

