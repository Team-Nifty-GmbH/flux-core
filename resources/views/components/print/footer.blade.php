{{--TODO: add fixed when printing--}}
<footer
    x-on:mousemove.window="isFooterClicked ? onMouseMoveFooter($event) : null"
    x-on:mouseup.window="onMouseUpFooter($event)"
    class="relative w-full bg-white text-center"
    x-data="printEditorFooter($data)"
    x-init="onInitFooter()"
    :class="editFooter ? 'border border-flux-primary-300' : ''"
    :style="{'min-height': footerHeight}"
>
{{--  footer height related  --}}
    <div
        x-cloak
        x-show="editFooter"
        x-on:mousedown="onMouseDownFooter($event,'footer')"
        class="absolute top-0 left-1/2 h-6 w-6 z-[100] -translate-x-1/2 -translate-y-1/2 cursor-pointer select-none rounded-full bg-flux-primary-400">
        <div
            class="relative flex h-full w-full items-center justify-center"
        >
            <div
                class="absolute bottom-8 h-12 rounded bg-gray-100 p-2 text-lg shadow"
                x-text="footerHeight"
            ></div>
        </div>
    </div>
{{--  footer height related  --}}
    <div class="footer-content h-full text-2xs leading-3">
        @section('footer.logo')
        <div
            class="absolute h-full left-0 right-0 z-[50]">
            <img
                draggable="false"
                :style="{'height': logoFooterSize}"
                class="logo-small footer-logo m-auto max-h-full"
                src="{{ $client->logo_small }}"
            />
        </div>
        @show
        <div class="w-full">
            <div class="border-semi-black border-t">
                @section('footer.client-address')
                <address
                    x-on:mousemove.window="isClientClicked ? onMouseMoveFooterClient($event) : null"
                    x-ref="client"
                    draggable="false"
                    :class="isClientClicked ? 'bg-flux-primary-300' : ''"
                    :style="{transform: `translate(${clientPositionLeft}, ${clientPositionTop})`}"
                    x-on:mousedown="editFooter ?  onMouseDownFooter($event, 'client') : null"
                    class="z-[100] select-none absolute text-left not-italic">
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
                @show
                @section('footer.bank-connections')
                @foreach ($client->bankConnections as $bankConnection)
                    <div class="absolute top-0 right-0 pl-3 text-left">
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
                    @if ($client->logo_small)
                        @break
                    @endif
                @endforeach

                @show
                <div class="clear-both"></div>
            </div>
        </div>
    </div>
</footer>
