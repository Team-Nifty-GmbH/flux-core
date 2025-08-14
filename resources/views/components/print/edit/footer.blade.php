<footer
    class="relative w-full bg-white text-center"
    x-on:mouseup.window="footerStore.onMouseUp()"
    x-on:mousemove.window="
        footerStore.selectedElementId !== null && !footerStore.isImgResizeClicked
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
    {{-- UI position of a selected element --}}
    <div x-cloak x-show="!footerStore.isImgResizeClicked && footerStore.selectedElementId !== null"
         :style="{'transform': `translate(${footerStore.selectedElementPos.x -50}px,${footerStore.selectedElementPos.y}px)` }"
         class="absolute left-0 top-0 z-[100] rounded shadow p-2 bg-gray-100">
        <div x-text="`${roundToOneDecimal(footerStore.selectedElementPos.x / footerStore.pxPerCm)}cm`"></div>
    </div>
    <div x-cloak x-show="!footerStore.isImgResizeClicked && footerStore.selectedElementId !== null"
         :style="{'transform': `translate(${footerStore.selectedElementPos.x}px,${footerStore.selectedElementPos.y - 40}px)` }"
         class="absolute left-0 top-0 z-[100] rounded shadow p-2 bg-gray-100">
        <div x-text="`${roundToOneDecimal(footerStore.selectedElementPos.y / footerStore.pyPerCm)}cm`"></div>
    </div>
    {{-- UI position of a selected element --}}
    <div
        x-ref="footer"
        class="footer-content relative h-full text-2xs leading-3"
        :style="`height: ${footerStore.footerHeight};`"
        x-on:mouseup.window="footerStore.onMouseUpFooter($event)"
        x-on:mousemove.window="footerStore.isFooterClicked ? footerStore.onMouseMoveFooter($event) : false"
    >
        <div
            x-on:mouseup.window="footerStore.onMouseUpResize($event)"
            x-on:mousemove.window="footerStore.isImgResizeClicked ? footerStore.onMouseMoveResize($event) : false"
            class="border-semi-black w-full border-t">
            <template
                id="{{ $client->id }}"
                x-ref="footer-client-{{ $client->id }}">
                <address
                    draggable="false"
                    data-type="container"
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
            <template
                id="{{ $client->id }}"
                x-ref="footer-logo">
                <div
                    id="footer-logo"
                    draggable="false"
                    data-type="img"
                    x-on:mousedown="printStore.editFooter ?  footerStore.onMouseDown($event, 'footer-logo') : null"
                    class="absolute left-0 top-0 h-[1.7cm] select-none"
                    :class="{'bg-gray-300' : !footerStore.isImgResizeClicked && footerStore.selectedElementId === 'footer-logo'}"
                >
                    <div
                        draggable="false"
                        x-cloak x-show="printStore.editFooter" class="relative w-full">
                        <x-icon
                            x-on:mousedown.stop="footerStore.onMouseDownResize($event, 'footer-logo')"
                            name="arrows-pointing-out" class="absolute cursor-pointer right-0 top-0 h-4 w-4 rounded-full"></x-icon>
                    </div>
                    <img
                        draggable="false"
                        class="logo-small footer-logo max-h-full w-full"
                        src="{{ $client->logo_small_url }}"
                    />
                </div>
            </template>
            @foreach ($client->bankConnections as $index => $bankConnection)
                <template
                    id="{{ uniqid() }}"
                    x-ref="footer-bank-{{ $bankConnection->id }}">
                    <div
                        id="footer-bank-{{ $bankConnection->id }}"
                        draggable="false"
                        data-type="container"
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
            <template
                id="{{ uniqid() }}"
                x-ref="footer-additional-img"
            >
                <img
                    id="footer-img-placeholder"
                    x-on:mousedown="printStore.editFooter ?  footerStore.onMouseDown($event,$event.target.id) : null"
                    data-type="img"
                    draggable="false"
                    class="absolute left-0 top-0 max-h-[1.7cm] select-none"
                    src=""
                     />
            </template>
            <div class="clear-both"></div>
        </div>
    </div>
</footer>
