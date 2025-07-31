<div

    x-init="firstPageHeaderStore.register($wire, $refs)"
    class="relative w-full">
    <div
        x-on:mouseup.window="firstPageHeaderStore.onMouseUpFirstPageHeader($event)"
        x-on:mousemove.window="
            firstPageHeaderStore.isFirstPageHeaderClicked
                ? firstPageHeaderStore.onMouseMoveFirstPageHeader($event)
                : null
        "
        x-ref="first-page-header"
        class="h-[7cm]"
        :style="`height: ${firstPageHeaderStore.height};`"
    >

    </div>
    {{-- UI - first page header - height related --}}
    <div
        x-cloak
        x-show="printStore.editFirstPageHeader"
        class="absolute bottom-0 w-full border-t border-t-gray-200"
    ></div>
    <div
        x-cloak
        x-show="printStore.editFirstPageHeader"
        class="absolute top-0 w-full border-b border-b-gray-200"
    ></div>
    <div
        x-on:mousedown="firstPageHeaderStore.onMouseDownFirstPageHeader($event)"
        x-cloak
        x-show="printStore.editFirstPageHeader"
        class="absolute bottom-0 left-1/2 z-[100] h-6 w-6 -translate-x-1/2 translate-y-1/2 cursor-pointer select-none rounded-full bg-flux-primary-400"
    >
        <div
            class="relative bottom-0 flex h-full w-full items-center justify-center"
        >
            <div
                x-text="firstPageHeaderStore.height"
                class="absolute -bottom-14 h-12 rounded bg-gray-100 p-2 text-lg shadow"
            ></div>
        </div>
    </div>
    {{-- UI - first page header - height related --}}
    <template
        id="{{ $client->id }}"
        x-ref="first-page-header-client-name"
    >
            <div
                id="first-page-header-client-name"
                class="absolute left-0 top-0 text-5xl font-semibold">
                {{ $client->name }}
            </div>
    </template>
    <template
        id="{{ $client->id }}"
        x-ref="first-page-header-postal-address-one-line"
    >
        <div
            id="first-page-header-postal-address-one-line"
            class="absolute left-0 top-0 text-2xs w-fit">
            <div>
                {{ $client->postal_address_one_line }}
            </div>
            <div class="w-full h-[1px] bg-black"></div>
        </div>
    </template>
    <template
        id="{{ $client->id }}"
        x-ref="first-page-header-address"
    >
        <address
            id="first-page-header-address"
            class="absolute left-0 top-0 text-xs not-italic">
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
    </template>
{{--    <template>--}}
{{--        <div class="absolute left-0 top-0 text-xs">--}}
{{--            {{ $rightBlock ?? '' }}--}}
{{--        </div>--}}
{{--    </template>--}}
    <template
        id="{{ $client->id }}"
        x-ref="first-page-header-subject"
    >
        <h1
            id="first-page-header-subject"
            class="absolute left-0 top-0 text-xl font-semibold">
            {{ $subject ?? '' }}
        </h1>
    </template>
    <template
        id="{{ $client->id }}"
        x-ref="first-page-header-right-block"
    >
        <table
            id="first-page-header-right-block"
            class="absolute left-0 top-0 w-[7cm]">
            <tbody class="align-text-top text-xs leading-none">
                <tr class="leading-none">
                    <td class="text-left font-semibold">
                        {{ __('Order no.') }}
                    </td>
                    <td class="text-left">
                        {{ $model->order_number }}
                    </td>
                </tr>
                <tr class="leading-none">
                    <td class="text-left font-semibold">
                        {{ __('Customer no.') }}
                    </td>
                    <td class="text-left">
                        {{ $model->customer_number }}
                    </td>
                </tr>
                <tr class="leading-none">
                    <td class="text-left font-semibold">
                        {{ __('Order Date') }}
                    </td>
                    <td class="text-left">
                        {{ $model->order_date->locale(app()->getLocale())->isoFormat('L') }}
                    </td>
                </tr>
                @if ($model->commission)
                    <tr class="leading-none">
                        <td class="py-0 text-left font-semibold">
                            {{ __('Commission') }}
                        </td>
                        <td class="py-0 text-left">
                            {{ $model->commission }}
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>
    </template>
</div>
