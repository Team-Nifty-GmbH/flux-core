<footer
    x-init="footerStore.register($wire, $refs)"
    class="relative w-full bg-white text-center"
    x-on:mouseup.window="footerStore.onMouseUp()"
    x-on:mousemove.window="
        footerStore.selectedElementId !== null
            ? footerStore.onMouseMove($event)
            : null
    "
>
    {{-- UI  footer height related --}}
    <div
        x-on:mousedown="footerStore.onMouseDownFooter($event)"
        x-cloak
        x-show="printStore.editFooter"
        class="absolute left-1/2 top-0 z-[100] h-6 w-6 -translate-x-1/2 -translate-y-1/2 cursor-pointer select-none rounded-full bg-flux-primary-400"
    >
         <div class="relative flex h-full w-full items-center justify-center">
         <div
             x-text="footerStore.footerHeight"
         class="absolute bottom-8 h-12 rounded bg-gray-100 p-2 text-lg shadow"
         ></div>
         </div>
    </div>
    {{-- UI  footer height related --}}
    <div
        x-ref="footer"
        class="footer-content relative h-full text-2xs leading-3"
        :style="`height: ${footerStore.footerHeight};`"
        x-on:mouseup.window="footerStore.onMouseUpFooter($event)"
        x-on:mousemove.window="footerStore.isFooterClicked ? footerStore.onMouseMoveFooter($event) : false"
    >
        <div class="border-semi-black w-full border-t">
            <template x-ref="footer-client-{{ $client->id }}">
                <address
                    x-on:mousedown="printStore.editFooter ?  footerStore.onMouseDown($event,'footer-client-{{ $client->id }}') : null"
                    id="footer-client-{{ $client->id }}"
                    class="absolute left-0 top-0 w-fit cursor-pointer select-none text-left not-italic"
                    :class="{'bg-gray-300' : footerStore.selectedElementId === 'footer-client-{{ $client->id }}'}"
                >
                    <div class="font-semibold">
                        {{ $client->name ?? '' }}
                    </div>
                    <div>
                        {{ $client->ceo ?? '' }}
                    </div>
                    <div>
                        {{ $client->street ?? '' }}
                    </div>
                    <div>
                        {{ trim(($client->postcode ?? '') . ' ' . ($client->city ?? '')) }}
                    </div>
                    <div>
                        {{ $client->phone ?? '' }}
                    </div>
                    <div>
                        <div>
                            {{ $client->vat_id }}
                        </div>
                    </div>
                </address>
            </template>
            <template x-ref="footer-logo">
                <div
                    id="footer-logo"
                    x-on:mousedown="printStore.editFooter ?  footerStore.onMouseDown($event, 'footer-logo') : null"
                    class="absolute left-0 top-0 h-[1.7cm] w-fit"
                    :class="{'bg-gray-300' : footerStore.selectedElementId === 'footer-logo'}"
                >
                    <img
                        draggable="false"
                        class="logo-small footer-logo max-h-full w-fit"
                        src="{{ $client->logo_small_url }}"
                    />
                </div>
            </template>
            @foreach ($client->bankConnections as $index => $bankConnection)
                <template x-ref="footer-bank-{{ $bankConnection->id }}">
                    <div
                        id="footer-bank-{{ $bankConnection->id }}"
                        x-on:mousedown="printStore.editFooter ?  footerStore.onMouseDown($event,'footer-bank-{{ $bankConnection->id }}') : null"
                        class="absolute left-0 top-0 w-fit cursor-pointer select-none text-left"
                        :class="{'bg-gray-300' : footerStore.selectedElementId === 'footer-bank-{{ $bankConnection->id }}'}"
                    >
                        <div class="font-semibold">
                            {{ $bankConnection->bank_name ?? '' }}
                        </div>
                        <div>
                            {{ $bankConnection->iban ?? '' }}
                        </div>
                        <div>
                            {{ $bankConnection->bic ?? '' }}
                        </div>
                    </div>
                </template>
            @endforeach

            <div class="clear-both"></div>
        </div>
    </div>
</footer>
