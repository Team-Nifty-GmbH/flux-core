<header class="font-light bg-white w-full h-auto text-center fixed">
    <div class="header-content">
        <div>
            <div class="text-left inline-block float-left">
                <h2 class="text-xl font-semibold">
                    {{ $subject ?? '' }}
                </h2>
                <div class="page-count text-xs"></div>
            </div>
            <div class="text-right max-h-72 w-44 inline-block float-right">
                <img class="logo-small" src="{{ $client->logo_small }}"  alt="logo-small"/>
            </div>
            <div class="clear-both"></div>
        </div>
    </div>
</header>
