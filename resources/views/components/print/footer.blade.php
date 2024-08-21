<footer class="bg-white w-full h-auto text-center fixed">
    <div class="footer-content text-2xs leading-3">
        @section('logo')
            <div class="absolute left-0 right-0 m-auto max-h-32 px-6">
                <img class="logo-small m-auto footer-logo" src="{{ $client->logo_small }}" />
            </div>
        @show
        <div class="w-full">
            <div class="border-t border-semi-black">
                @section('address')
                    <address class="text-left not-italic float-left">
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
                @section('bank-connections')
                    @foreach($client->bankConnections as $bankConnection)
                        <div class="float-right text-left pl-3">
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
                        @if($client->logo_small)
                            @break
                        @endif
                    @endforeach
                @endsection
                <div class="clear-both"></div>
            </div>
        </div>
    </div>
</footer>
