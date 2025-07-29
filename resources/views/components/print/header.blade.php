{{-- TODO: add fixed when printing --}}
<header
    class="h-[1.7cm] w-full bg-white text-center font-light"
    :class="$store.printStore.editHeader ? 'border border-flux-primary-300' : ''"
>
    <div class="header-content">
        <div>
            <div class="float-left inline-block text-left">
                <h2 class="text-xl font-semibold">
                    {{ $subject ?? '' }}
                </h2>
                <div class="page-count text-xs"></div>
            </div>
            <div class="float-right inline-block max-h-full w-44 text-right">
                <img
                    class="logo-small"
                    src="{{ $client->logo_small_url }}"
                    alt="logo-small"
                />
            </div>
        </div>
    </div>
</header>
