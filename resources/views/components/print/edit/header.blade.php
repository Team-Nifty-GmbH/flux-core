<header
    x-init="headerStore.register($wire, $refs)"
    class="h-[1.7cm] w-full bg-white text-center relative"
    x-on:mouseup.window="headerStore.onMouseUp()"
    x-on:mousemove.window="
        headerStore.selectedElementId !== null && !headerStore.isImgResizeClicked
            ? headerStore.onMouseMove($event)
            : null
    "
    :style="`height: ${headerStore.headerHeight};`"
>
    {{-- UI  footer height related --}}
    <div
        x-on:mousedown="headerStore.onMouseDownHeader($event)"
        x-cloak
        x-show="printStore.editHeader"
        class="absolute left-1/2 bottom-0 z-[100] h-6 w-6 -translate-x-1/2 translate-y-1/2 cursor-pointer select-none rounded-full bg-flux-primary-400"
    >
        <div class="bottom-0 relative flex h-full w-full items-center justify-center">
            <div
                x-text="headerStore.headerHeight"
                class="absolute -bottom-14  h-12 rounded bg-gray-100 p-2 text-lg shadow"
            ></div>
        </div>
    </div>
    <div
        x-cloak
        x-show="printStore.editHeader"
        class="absolute bottom-0 border-b w-full border-b-gray-300"></div>
    {{-- UI  footer height related --}}
    {{-- UI position of a selected element --}}
    <div x-cloak x-show="!headerStore.isImgResizeClicked && headerStore.selectedElementId !== null"
         :style="{'transform': `translate(${headerStore.selectedElementPos.x -50}px,${headerStore.selectedElementPos.y}px)` }"
         class="absolute left-0 top-0 z-[100] rounded shadow p-2 bg-gray-100">
        <div x-text="`${roundToOneDecimal(headerStore.selectedElementPos.x / headerStore.pxPerCm)}cm`"></div>
    </div>
    <div x-cloak x-show="!headerStore.isImgResizeClicked && headerStore.selectedElementId !== null"
         :style="{'transform': `translate(${headerStore.selectedElementPos.x}px,${headerStore.selectedElementPos.y - 40}px)` }"
         class="absolute left-0 top-0 z-[100] rounded shadow p-2 bg-gray-100">
        <div x-text="`${roundToOneDecimal(headerStore.selectedElementPos.y / headerStore.pyPerCm)}cm`"></div>
    </div>
    {{-- UI position of a selected element --}}
    <div
        x-ref="header"
        x-on:mouseup.window="headerStore.onMouseUpHeader($event)"
        x-on:mousemove.window="headerStore.isHeaderClicked ? headerStore.onMouseMoveHeader($event) : null"
        class="header-content relative h-full w-full">
    </div>
    <div
        x-on:mouseup.window="headerStore.onMouseUpResize($event)"
        x-on:mousemove.window="headerStore.isImgResizeClicked ? headerStore.onMouseMoveResize($event) : null"
    ></div>
    <template
        id="{{ $client->id }}"
        x-ref="header-subject"
    >
        <div
            id="header-subject"
            draggable="false"
            data-type="container"
            class="absolute left-0 top-0 w-fit select-none"
            :class="{'bg-gray-300' : !headerStore.isImgResizeClicked && headerStore.selectedElementId === 'header-subject'}"
            x-on:mousedown="printStore.editHeader ?  headerStore.onMouseDown($event, 'header-subject') : null"
        >
            <h2 class="text-xl font-semibold">
                {{ $subject ?? '' }}
            </h2>
        </div>
    </template>
    <template
        id="{{ $client->id }}"
        x-ref="header-logo"
    >
        <div
            id="header-logo"
            draggable="false"
            data-type="img"
            x-on:mousedown="printStore.editHeader ?  headerStore.onMouseDown($event, 'header-logo') : null"
            class="absolute left-0 top-0 h-[1.7cm] select-none"
            :class="{'bg-gray-300' : !headerStore.isImgResizeClicked && headerStore.selectedElementId === 'header-logo'}"
        >
            <div
                draggable="false"
                x-cloak x-show="printStore.editHeader" class="relative w-full">
                <x-icon
                    x-on:mousedown.stop="headerStore.onMouseDownResize($event, 'header-logo')"
                    name="arrows-pointing-out" class="absolute cursor-pointer right-0 top-0 h-4 w-4 rounded-full"></x-icon>
            </div>
            <img
                draggable="false"
                class="logo-small max-h-full w-full"
                alt="logo-small"
                src="{{ $client->logo_small_url }}"
            />
        </div>
    </template>
</header>
