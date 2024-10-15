<div class="cover-page h-auto overflow-auto z-10 bg-white">
    @section('first-page-logo')
        <div class="grid h-48 content-center">
            <div class="text-center m-auto max-h-72">
                @if($client->logo)
                    <img class="logo m-auto" src="{{ $client->logo }}" />
                @else
                    <div class="text-5xl font-semibold">
                        {{ $client->name }}
                    </div>
                @endif
            </div>
        </div>
    @show
    @section('client-address')
        <div class="-mt-2 w-full pb-1 text-2xs">
            {{ $client->name . ' | ' . $client->street . ' | ' . $client->postcode . ' ' . $client->city }}
        </div>
    @show
    <div class="black-bar"></div>
    <div class="block pt-20 ">
        @if($slot->isNotEmpty())
            {!! $slot !!}
        @else
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
        @endif
        <div class="inline-block float-right items-end float-right align-top">
            @section('right-block')
                <div>
                    {{ $rightBlock ?? '' }}
                </div>
            @show
        </div>
    </div>
    @section('first-page-subject')
        <h1 class="pt-32 text-xl font-semibold">
            {{ $subject ?? '' }}
        </h1>
    @show
</div>
