<footer
    x-init="$store.footerStore.register($wire,$refs)"
    class="relative w-full bg-white text-center h-[1.7cm]">
    {{-- UI  footer height related --}}
    <div
        x-cloak
        x-show="editFooter"
        x-on:mousedown="onMouseDownFooter($event, 'footer')"
        class="absolute left-1/2 top-0 z-[100] h-6 w-6 -translate-x-1/2 -translate-y-1/2 cursor-pointer select-none rounded-full bg-flux-primary-400"
    >
        <div class="relative flex h-full w-full items-center justify-center">
            <div
                class="absolute bottom-8 h-12 rounded bg-gray-100 p-2 text-lg shadow"
            ></div>
        </div>
    </div>
    {{-- UI  footer height related --}}
    <div class="footer-content h-full text-2xs leading-3">
        <div x-ref="footer-body" class="w-full">
            <div class="border-semi-black border-t">
                <template x-ref="footer-client-{{ $client->id }}">
                    <address>
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
                    <div>
                        <img
                            draggable="false"
                            class="logo-small footer-logo max-h-full"
                            src="{{ $client->logo_small_url }}"
                        />
                    </div>
                </template>
                @foreach ($client->bankConnections as $index => $bankConnection)
                    <template x-ref="bank-{{ $bankConnection->id }}">
                        <div class="absolute text-left">
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
    </div>
</footer>
