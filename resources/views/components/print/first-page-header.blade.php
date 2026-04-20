<div
    class="cover-page"
    style="z-index: 10; height: auto; overflow: auto; background: white"
>
    @section('first-page-logo')
        <div style="display: grid; height: 128px; align-content: center">
            <div style="margin: auto; max-height: 288px; text-align: center">
                @if($tenant->logo)
                    <img
                        class="logo"
                        style="margin: auto"
                        src="{{ $tenant->logo }}"
                    />
                @else
                    <div
                        style="
                            font-size: 48px;
                            line-height: 1;
                            font-weight: 600;
                        "
                    >
                        {{ $tenant->name }}
                    </div>
                @endif
            </div>
        </div>
    @show
    <table style="width: 100%">
        <tr>
            <td
                colspan="2"
                style="
                    font-size: 10px;
                    width: 100%;
                    padding-top: 24px;
                    padding-bottom: 4px;
                "
            >
                @section('tenant-address')
                    <div>{{ $tenant->postal_address_one_line }}</div>
                    <div class="black-bar"></div>
                @show
            </td>
        </tr>
        <tr style="height: 16px">
            <td colspan="2"></td>
        </tr>
        @section('recipient-address')
            <tr>
                <td style="width: 50%; vertical-align: top">
                    @section('recipient-address.left-block')
                        @if($slot->isNotEmpty())
                            {!! $slot !!}
                        @else
                            <address
                                style="font-size: 12px; font-style: normal"
                            >
                                <div style="font-weight: 600">
                                    {{ $address->company ?? '' }}
                                </div>
                                <div>
                                    {{ trim(($address->firstname ?? '') . ' ' . ($address->lastname ?? '')) }}
                                </div>
                                <div>{{ $address->addition ?? '' }}</div>
                                <div>{{ $address->street ?? '' }}</div>
                                <div>
                                    {{ trim(($address->zip ?? '') . ' ' . ($address->city ?? '')) }}
                                </div>
                                @if($address->country_name ?? null)
                                    <div>{{ $address->country_name }}</div>
                                @endif
                            </address>
                        @endif
                    @show
                </td>
                <td style="width: 50%; vertical-align: top">
                    @section('recipient-address.right-block')
                        <div
                            style="
                                float: right;
                                display: inline-block;
                                max-width: 100%;
                                font-size: 12px;
                            "
                        >
                            {{ $rightBlock ?? '' }}
                        </div>
                    @show
                </td>
            </tr>
        @show
    </table>
    @section('first-page-subject')
        <h1
            style="
                padding-top: 80px;
                font-size: 20px;
                line-height: 28px;
                font-weight: 600;
            "
        >
            {{ $subject ?? '' }}
        </h1>
    @show
</div>
