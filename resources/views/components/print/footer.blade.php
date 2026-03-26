<footer class="fixed h-auto w-full bg-white text-center">
    <div class="footer-content text-2xs leading-3">
        @section('footer.logo')
        <div class="absolute left-0 right-0 m-auto max-h-32 px-6">
            <img
                class="logo-small footer-logo m-auto"
                src="{{ $tenant->logo_small }}"
            />
        </div>
        @show
        <div class="w-full">
            <div class="border-semi-black border-t">
                @section('footer.tenant-address')
                <address class="float-left text-left not-italic">
                    <div class="font-semibold">
                        {{ $tenant->name ?? '' }}
                    </div>
                    <div>
                        {{ $tenant->ceo ?? '' }}
                    </div>
                    <div>
                        {{ $tenant->street ?? '' }}
                    </div>
                    <div>
                        {{ trim(($tenant->postcode ?? '') . ' ' . ($tenant->city ?? '')) }}
                    </div>
                    <div>
                        {{ $tenant->phone ?? '' }}
                    </div>
                    <div>
                        <div>
                            {{ $tenant->vat_id }}
                        </div>
                    </div>
                </address>
                @show
                @section('footer.bank-connections')
                @foreach ($tenant->bankConnections as $bankConnection)
                    <div class="float-right pl-3 text-left">
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
                    @if ($tenant->logo_small)
                        @break
                    @endif
                @endforeach

                @show
                <div class="clear-both"></div>
            </div>
        </div>
    </div>
</footer>
