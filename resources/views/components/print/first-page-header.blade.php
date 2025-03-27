<div class="cover-page z-10 h-auto overflow-auto bg-white">
    @section('first-page-logo')
    <div class="grid h-32 content-center">
        <div class="m-auto max-h-72 text-center">
            @if ($client->logo)
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
    <div class="text-2xs -mt-2 w-full pb-1">
        {{ $client->postal_address_one_line }}
    </div>
    <div class="black-bar"></div>
    @show
    @section('recipient-address')
    <div class="block pb-16 pt-4">
        @section('recipient-address.left-block')
        @if ($slot->isNotEmpty())
            {!! $slot !!}
        @else
            <address class="float-left inline-block align-top not-italic">
                <div class="font-semibold">
                    {{ $address->company ?? '' }}
                </div>
                <div>
                    {{ trim(($address->firstname ?? '') . ' ' . ($address->lastname ?? '')) }}
                </div>
                <div>
                    {{ $address->addition ?? '' }}
                </div>
                <div>
                    {{ $address->street ?? '' }}
                </div>
                <div>
                    {{ trim(($address->zip ?? '') . ' ' . ($address->city ?? '')) }}
                </div>
                <div>
                    {{ $address->country->name ?? '' }}
                </div>
            </address>
        @endif
        @show
        <div class="float-right inline-block items-end align-top">
            @section('recipient-address.right-block')
            <div>
                {{ $rightBlock ?? '' }}
            </div>
            @show
        </div>
    </div>
    @show
    @section('first-page-subject')
    <h1 class="pt-32 text-xl font-semibold">
        {{ $subject ?? '' }}
    </h1>
    @show
</div>
