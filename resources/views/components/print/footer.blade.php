<footer
    x-data="printEditorFooter($data,$store.footerStore)"
    x-init="$store.footerStore.register($wire, $refs)"
    class="relative w-full bg-white text-center"
    x-on:mouseup.window="$store.footerStore.onMouseUp()"
    x-on:mousemove.window="
        $store.footerStore.selectedElementId !== null
            ? $store.footerStore.onMouseMove($event)
            : null
    "
>
    {{-- UI  footer height related --}}
    <div
        x-on:mousedown="onMouseDownFooter($event, 'footer')"
        x-cloak
        x-show="editFooter"
        class="absolute left-1/2 top-0 z-[100] h-6 w-6 -translate-x-1/2 -translate-y-1/2 cursor-pointer select-none rounded-full bg-flux-primary-400"
    >
        {{-- <div class="relative flex h-full w-full items-center justify-center"> --}}
        {{-- <div --}}
        {{-- class="absolute bottom-8 h-12 rounded bg-gray-100 p-2 text-lg shadow" --}}
        {{-- ></div> --}}
        {{-- </div> --}}
    </div>
    {{-- UI  footer height related --}}
    <div
        x-ref="footer"
        class="footer-content relative h-full text-2xs leading-3"
        :style="`height: ${footerHeight};`"
        x-on:mouseup.window="onMouseUpFooter"
        x-on:mousemove.window="isFooterClicked ? onMouseMoveFooter : false"
    >
        <div class="border-semi-black w-full border-t">
            <template x-ref="footer-client-{{ $client->id }}">
                <address
                    x-on:mousedown="$store.footerStore.onMouseDown($event,'footer-client-{{ $client->id }}')"
                    id="footer-client-{{ $client->id }}"
                    class="absolute left-0 top-0 w-fit cursor-pointer select-none text-left not-italic"
                    :class="{'bg-gray-300' : $store.footerStore.selectedElementId === 'footer-client-{{ $client->id }}'}"
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
                    x-on:mousedown="$store.footerStore.onMouseDown($event, 'footer-logo')"
                    class="absolute left-0 top-0 h-[1.7cm] w-fit"
                    :class="{'bg-gray-300' : $store.footerStore.selectedElementId === 'footer-logo'}"
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
                        x-on:mousedown="$store.footerStore.onMouseDown($event,'footer-bank-{{ $bankConnection->id }}')"
                        class="absolute left-0 top-0 w-fit cursor-pointer select-none text-left"
                        :class="{'bg-gray-300' : $store.footerStore.selectedElementId === 'footer-bank-{{ $bankConnection->id }}'}"
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
