<header class="h-auto w-full bg-white text-center font-light">
    <div class="header-content">
        <div>
                <div class="float-left inline-block text-left">
                    <h2 class="text-xl font-semibold">
                        {{ $subject ?? '' }}
                    </h2>
                    <div class="page-count text-xs"></div>
                </div>
                <div class="float-right inline-block max-h-72 w-44 text-right">
                    @if($client->logo_small)
                    <img
                        class="logo-small"
                        src="{{ $client->logo_small }}"
                        alt="logo-small"
                    />
                    @endif
                </div>
            <div class="clear-both"></div>
        </div>
    </div>
</header>
