
@php
    $posCollection = new \Illuminate\Support\Collection($AUFTRÄGE[0]['POSITIONEN']);
@endphp

<x-slot name="header">
    <div class="following-pages-header">
        <div class="theadrow">
            <div class="tc-1">Anzahl</div>
            <div class="tc-2">Artikelnr. Extern</div>
            <div class="tc-3">Artikelnr. Intern</div>
            <div class="tc-4 th-single">Artikelbeschreibung</div>
            <div class="tc-5 th-single text-center">Rennergy EAN</div>
        </div>
    </div>
</x-slot>

<x-slot name="head">
    <style>
        .text-center {
            text-align: center;
        }
        .tc-1 {
            min-width: 70px;
        }

        .tc-2 {
            min-width: 126px;
        }

        .tc-3 {
            width: auto;
            min-width: 126px;
        }

        .tc-4 {
            min-width:200px;
        }

        .tc-5 {
            min-width: 118px;
        }

        .th-single {
            max-width: none;
        }

        tbody tr {
            border-top: 1px solid;
        }

        td {
            padding: 10px 10px;
        }

        .infoblock-right .infoblock-label {
            width: 180px;
        }

        .tel-avis {
            padding: 30px 30px 30px 0px;
        }
    </style>
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
            <div>Sachbearbeiter: {{ $AUFTRÄGE[0]['AUFTRAG_SACHBEARBEITER'] ?? '' }}</div>
            <div>Telefon: {{ $AUFTRÄGE[0]['AUFTRAG_SACHBEARBEITER_TELEFON'] ?? '' }}</div>
            <div>E-Mail: {{ $AUFTRÄGE[0]['AUFTRAG_SACHBEARBEITER_EMAIL'] ?? '' }}</div>
            <div class="tel-avis">Tel.-Avis: {{ $AUFTRÄGE[0]['LOGISTIK_ANWEISUNGEN_AVISIEREN'] ?? '' }}</div>
            <div class="subject">
                {{ $AUFTRÄGE[0]['AUFTRAG_BELEGTYP'] ?? '' }} {{ $AUFTRÄGE[0]['AUFTRAG_BELEGNUMMER'] ?? '' }} vom {{ $AUFTRÄGE[0]['AUFTRAG_BELEGDATUM'] ?? '' }}
            </div>
            <div class="infoblock-bottom">
                <div class="infoblock-left">
                    <div class="infoblock-td">
                        <div class="infoblock-label">Kommission: </div>
                        <div class="pl-10">{{ $AUFTRÄGE[0]['AUFTRAG_KOMMISSION'] ?? '' }}</div>
                    </div>
                </div>
                <div class="infoblock-right">
                    <div class="infoblock-td">
                        <div class="infoblock-label">Gewünschter Liefertermin: </div>
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
    <x-dynamic-component :component="'positions_custom.' . $sublayout ?? 'index'" :pos-collection="$posCollection" :auftrag="$AUFTRÄGE"></x-dynamic-component>

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

