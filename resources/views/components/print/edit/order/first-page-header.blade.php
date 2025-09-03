<div
    x-on:mouseup.window="firstPageHeaderStore.onMouseUp()"
    x-on:mousemove.window="
        firstPageHeaderStore.selectedElementId !== null && !firstPageHeaderStore.isImgResizeClicked
            ? firstPageHeaderStore.onMouseMove($event)
            : null
    "
    class="relative w-full box-border">
    <div
        x-on:mouseup.window="firstPageHeaderStore.onMouseUpFirstPageHeader($event)"
        x-on:mousemove.window="
            firstPageHeaderStore.isFirstPageHeaderClicked
                ? firstPageHeaderStore.onMouseMoveFirstPageHeader($event)
                : null
        "
        x-ref="first-page-header"
        class="h-[7cm] box-border"
        :style="`height: ${firstPageHeaderStore.height};`"
    >
        <div
            x-on:mouseup.window="firstPageHeaderStore.onMouseUpScale($event)"
            x-on:mousemove.window="firstPageHeaderStore.isImgResizeClicked ? firstPageHeaderStore.onMouseMoveScale($event) : false"
            class="w-0 h-0"></div>

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
    {{-- UI position of a selected element --}}
    <div x-cloak x-show="firstPageHeaderStore.selectedElementId !== null && !firstPageHeaderStore.isImgResizeClicked"
         :style="{'transform': `translate(${firstPageHeaderStore.selectedElementPos.x -50}px,${firstPageHeaderStore.selectedElementPos.y}px)` }"
         class="absolute left-0 top-0 z-[100] rounded shadow p-2 bg-gray-100">
        <div x-text="`${roundToOneDecimal(firstPageHeaderStore.selectedElementPos.x / firstPageHeaderStore.pxPerCm)}cm`"></div>
    </div>
    <div x-cloak x-show="firstPageHeaderStore.selectedElementId !== null && !firstPageHeaderStore.isImgResizeClicked"
         :style="{'transform': `translate(${firstPageHeaderStore.selectedElementPos.x}px,${firstPageHeaderStore.selectedElementPos.y - 40}px)` }"
         class="absolute left-0 top-0 z-[100] rounded shadow p-2 bg-gray-100">
        <div x-text="`${roundToOneDecimal(firstPageHeaderStore.selectedElementPos.y / firstPageHeaderStore.pyPerCm)}cm`"></div>
    </div>
    {{-- UI position of a selected element --}}
    <template
        id="{{ $client->id }}"
        x-ref="first-page-header-client-name"
    >
            <div
                id="first-page-header-client-name"
                data-type="container"
                draggable="false"
                class="absolute left-0 top-0 text-5xl font-semibold select-none"
                :class="{'bg-gray-300' : firstPageHeaderStore.selectedElementId === 'first-page-header-client-name'}"
                x-on:mousedown="printStore.editFirstPageHeader ?  firstPageHeaderStore.onMouseDown($event, 'first-page-header-client-name') : null"
            >
                {{ $client->name }}
            </div>
    </template>
    <template
        id="{{ $client->id }}"
        x-ref="first-page-header-postal-address-one-line"
    >
        <div
            id="first-page-header-postal-address-one-line"
            data-type="container"
            draggable="false"
            class="absolute left-0 top-0 text-2xs w-fit select-none"
            :class="{'bg-gray-300' : firstPageHeaderStore.selectedElementId === 'first-page-header-postal-address-one-line'}"
            x-on:mousedown="printStore.editFirstPageHeader ?  firstPageHeaderStore.onMouseDown($event, 'first-page-header-postal-address-one-line') : null"
        >
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
            data-type="container"
            draggable="false"
            class="absolute left-0 top-0 text-xs not-italic select-none"
            :class="{'bg-gray-300' : firstPageHeaderStore.selectedElementId === 'first-page-header-address'}"
            x-on:mousedown="printStore.editFirstPageHeader ?  firstPageHeaderStore.onMouseDown($event, 'first-page-header-address') : null"
        >
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
            data-type="container"
            draggable="false"
            class="absolute left-0 top-0 text-xl font-semibold select-none"
            :class="{'bg-gray-300' : firstPageHeaderStore.selectedElementId === 'first-page-header-subject'}"
            x-on:mousedown="printStore.editFirstPageHeader ?  firstPageHeaderStore.onMouseDown($event, 'first-page-header-subject') : null"
        >
            {{ $subject ?? '' }}
        </h1>
    </template>
    <template
        id="{{ $client->id }}"
        x-ref="first-page-header-right-block"
    >
        <table
            id="first-page-header-right-block"
            data-type="container"
            draggable="false"
            class="absolute left-0 top-0 w-[7cm] select-none"
            :class="{'bg-gray-300' : firstPageHeaderStore.selectedElementId === 'first-page-header-right-block'}"
            x-on:mousedown="printStore.editFirstPageHeader ?  firstPageHeaderStore.onMouseDown($event, 'first-page-header-right-block') : null"
        >
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
    <template
        id="{{ uniqid() }}"
        x-ref="first-page-header-additional-img"
    >
        <div
            id="first-page-header-img-placeholder"
            x-on:mousedown="printStore.editFirstPageHeader ?  firstPageHeaderStore.onMouseDown($event,$el.id,'temporary') : null"
            data-type="resizable"
            draggable="false"
            class="absolute left-0 top-0 select-none h-[1.7cm]"
            :class="{'bg-gray-300' : !firstPageHeaderStore.isImgResizeClicked && firstPageHeaderStore.selectedElementId === $el.id}"
        >
            <div
                draggable="false"
                x-cloak x-show="printStore.editFirstPageHeader" class="relative w-full">
                <x-icon
                    x-on:mousedown.stop="firstPageHeaderStore.onMouseDownScale($event, $el.parentElement.parentElement.id,'temporary')"
                    name="arrows-pointing-out" class="absolute cursor-pointer right-0 top-0 h-4 w-4 rounded-full"></x-icon>
            </div>
            <img
                draggable="false"
                class="max-h-full w-full"
                src=""
            />
        </div>
    </template>
    <template
        id="{{ uniqid() }}"
        x-ref="first-page-header-media">
        <div
            id="first-page-header-media"
            x-on:mousedown="printStore.editFirstPageHeader ?  firstPageHeaderStore.onMouseDown($event,$el.id,'media') : null"
            data-type="resizable"
            draggable="false"
            class="absolute left-0 top-0 select-none h-[1.7cm]"
            :class="{'bg-gray-300' : !firstPageHeaderStore.isImgResizeClicked && firstPageHeaderStore.selectedElementId === $el.id}"
        >
            <div
                draggable="false"
                x-cloak x-show="printStore.editFirstPageHeader" class="relative w-full">
                <x-icon
                    x-on:mousedown.stop="firstPageHeaderStore.onMouseDownScale($event, $el.parentElement.parentElement.id,'media')"
                    name="arrows-pointing-out" class="absolute cursor-pointer right-0 top-0 h-4 w-4 rounded-full"></x-icon>
            </div>
            <img
                draggable="false"
                class="max-h-full w-full"
                src=""
            />
        </div>
    </template>
</div>
