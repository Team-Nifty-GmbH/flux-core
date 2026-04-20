<footer
    style="
        position: fixed;
        height: auto;
        width: 100%;
        background: white;
        text-align: center;
    "
>
    <div class="footer-content" style="font-size: 10px; line-height: 12px">
        @section('footer.logo')
            <div
                style="
                    position: absolute;
                    right: 0;
                    left: 0;
                    margin: auto;
                    max-height: 128px;
                    padding-left: 24px;
                    padding-right: 24px;
                "
            >
                <img
                    class="logo-small footer-logo"
                    style="margin: auto"
                    src="{{ $tenant->logo_small }}"
                />
            </div>
        @show
        <div style="width: 100%">
            <div style="border-top: 1px solid #6b7280">
                @section('footer.tenant-address')
                    <address
                        style="
                            float: left;
                            text-align: left;
                            font-style: normal;
                        "
                    >
                        <div style="font-weight: 600">
                            {{ $tenant->name ?? '' }}
                        </div>
                        <div>{{ $tenant->ceo ?? '' }}</div>
                        <div>{{ $tenant->street ?? '' }}</div>
                        <div>
                            {{ trim(($tenant->postcode ?? '') . ' ' . ($tenant->city ?? '')) }}
                        </div>
                        <div>{{ $tenant->phone ?? '' }}</div>
                        <div>
                            <div>{{ $tenant->vat_id }}</div>
                        </div>
                    </address>
                @show
                @section('footer.bank-connections')
                    @foreach($tenant->bankConnections as $bankConnection)
                        <div
                            style="
                                float: right;
                                padding-left: 12px;
                                text-align: left;
                            "
                        >
                            <div style="font-weight: 600">
                                {{ $bankConnection->bank_name ?? '' }}
                            </div>
                            <div>{{ $bankConnection->iban ?? '' }}</div>
                            <div>{{ $bankConnection->bic ?? '' }}</div>
                        </div>
                        @if($tenant->logo_small)
                            @break
                        @endif
                    @endforeach

                @show
                <div style="clear: both"></div>
            </div>
        </div>
    </div>
</footer>
