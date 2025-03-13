<footer class="fixed h-auto w-full bg-white text-center">
    <div class="footer-content text-2xs leading-3">
        @section("footer.logo")
        <div class="absolute left-0 right-0 m-auto max-h-32 px-6">
            <img
                class="logo-small footer-logo m-auto"
                src="{{ $client->logo_small }}"
            />
        </div>
        @show
        <div class="w-full">
            <div class="border-semi-black border-t">
                @section("footer.client-address")
                <address class="float-left text-left not-italic">
                    <div class="font-semibold">
                        {{ $client->name ?? "" }}
                    </div>
                    <div>
                        {{ $client->ceo ?? "" }}
                    </div>
                    <div>
                        {{ $client->street ?? "" }}
                    </div>
                    <div>
                        {{ trim(($client->postcode ?? "") . " " . ($client->city ?? "")) }}
                    </div>
                    <div>
                        {{ $client->phone ?? "" }}
                    </div>
                    <div>
                        <div>
                            {{ $client->vat_id }}
                        </div>
                    </div>
                </address>
                @show
                @section("footer.bank-connections")
                @foreach ($client->bankConnections as $bankConnection)
                    <div class="float-right pl-3 text-left">
                        <div class="font-semibold">
                            {{ $bankConnection->bank_name ?? "" }}
                        </div>
                        <div>
                            {{ $bankConnection->iban ?? "" }}
                        </div>
                        <div>
                            {{ $bankConnection->bic ?? "" }}
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
