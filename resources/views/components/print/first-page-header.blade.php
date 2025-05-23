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
    <table class="w-full">
        <tr>
            <td colspan="2" class="w-full pb-1 pt-6 text-2xs">
                @section('client-address')
                <div>
                    {{ $client->postal_address_one_line }}
                </div>
                <div class="black-bar"></div>
                @show
            </td>
        </tr>
        <tr class="h-4">
            <td colspan="2"></td>
        </tr>
        @section('recipient-address')
        <tr>
            <td class="w-1/2 align-top">
                @section('recipient-address.left-block')
                @if ($slot->isNotEmpty())
                    {!! $slot !!}
                @else
                    <address class="text-xs not-italic">
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
                    </address>
                @endif
                @show
            </td>
            <td class="w-1/2 align-top">
                @section('recipient-address.right-block')
                <div class="float-right inline-block max-w-full text-xs">
                    {{ $rightBlock ?? '' }}
                </div>
                @show
            </td>
        </tr>
        @show
    </table>
    @section('first-page-subject')
    <h1 class="pt-20 text-xl font-semibold">
        {{ $subject ?? '' }}
    </h1>
    @show
</div>
