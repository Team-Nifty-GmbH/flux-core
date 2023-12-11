<div class="cover-page h-auto overflow-auto z-10 bg-white">
    <div class="grid h-48 content-center">
        <div class="text-center m-auto max-h-72 w-72">
            <img class="logo m-auto" src="{{ $client->logo }}" />
        </div>
    </div>
    <div class="-mt-2 w-full pb-1 text-xs">
        {{ $client->name . ' | ' . $client->street . ' | ' . $client->zip . ' ' . $client->city }}
    </div>
    <div class="black-bar"></div>
    <div class="block pt-20 ">
        <address class="inline-block not-italic float-left align-top">
            <div class="font-semibold">
                {{ $address->company ?? '' }}
            </div>
            <div>
                {{ trim(($address->firstname ?? '') . ' ' . ($address->lastname ?? '')) }}
            </div>
            <div>
                {{ $address->street ?? '' }}
            </div>
            <div>
                {{ trim(($address->zip ?? '') . ' ' . ($address->city ?? '')) }}
            </div>
        </address>
        <div class="inline-block float-right items-end float-right align-top">
            <div>
                {{ $rightBlock ?? '' }}
            </div>
        </div>
    </div>
    <h1 class="pt-32 text-2xl font-semibold">
        {{ $subject ?? '' }}
    </h1>
</div>
