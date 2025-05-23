<header class="fixed h-auto w-full bg-white text-center font-light">
    <div class="header-content">
        <div>
            @section('subject')
            <div class="float-left inline-block text-left">
                <h2 class="text-xl font-semibold">
                    {{ $subject ?? '' }}
                </h2>
                <div class="page-count text-xs"></div>
            </div>
            @show
            @section('logo')
            <div class="float-right inline-block max-h-72 w-44 text-right">
                <img
                    class="logo-small"
                    src="{{ $client->logo_small }}"
                    alt="logo-small"
                />
            </div>
            @show
            <div class="clear-both"></div>
        </div>
    </div>
</header>
