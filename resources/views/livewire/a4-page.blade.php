<div
    x-data="printEditor()"
    x-init="onInit"
    class="min-w-[21cm] w-[21cm] min-h-[29.7cm] h-[29.7cm] bg-white shadow">
    <div
        x-on:mousemove.window="isAnyClicked ? onMouseMove($event) : null"
        x-on:mouseup.window="onMouseUp($event)"
        class="w-full h-full bg-primary-300" :style="{'padding-left': marginLeft, 'padding-right': marginRight, 'padding-top': marginTop, 'padding-bottom': marginBottom}">
        {{-- content --}}
        <div class="relative w-full h-full bg-white">
            {{-- scale for converting cm to px --}}
            <div
                x-ref="scale"
                class="absolute top-0 left-0 w-[1cm] h-[1cm]"></div>
            <div
                x-on:mousedown="onMouseDown($event,'margin-top')"
                :class="{'bg-flux-primary-500': isTopClicked, 'bg-flux-primary-700': !isTopClicked}"
                draggable="false"
                class=" absolute cursor-pointer shadow select-none rounded-full  w-6 h-6 top-0 left-1/2 -translate-x-1/2 -translate-y-1/2">
            </div>
            <div class="absolute top-0 left-[55%] -translate-y-1/2 p-2 rounded h-12 text-lg bg-gray-100 shadow" x-text="marginTop"></div>
            <div
                x-on:mousedown="onMouseDown($event,'margin-left')"
                :class="{'bg-flux-primary-500': isLeftClicked, 'bg-flux-primary-700': !isLeftClicked}"
                draggable="false"
                class="cursor-pointer select-none rounded-full bg-flux-primary-700 absolute w-6 h-6 left-0 top-1/2 -translate-x-1/2 -translate-y-1/2"></div>
            <div class="absolute  left-[-10%] top-[50%] -translate-y-1/2 p-2 rounded h-12 text-lg bg-gray-100 shadow" x-text="marginLeft"></div>
            <div
                x-on:mousedown="onMouseDown($event,'margin-bottom')"
                :class="{'bg-flux-primary-500': isBottomClicked, 'bg-flux-primary-700': !isBottomClicked}"
                class="cursor-pointer select-none rounded-full absolute w-6 h-6 bottom-0 left-1/2 -translate-x-1/2 translate-y-1/2"></div>
            <div class="absolute bottom-0 left-[55%] translate-y-1/2 p-2 rounded h-12 text-lg bg-gray-100 shadow" x-text="marginBottom"></div>
            <div
                x-on:mousedown="onMouseDown($event,'margin-right')"
                draggable="false"
                :class="{'bg-flux-primary-500': isRightClicked, 'bg-flux-primary-700': !isRightClicked}"
                class="cursor-pointer select-none rounded-full absolute w-6 h-6 right-0 top-1/2 translate-x-1/2 -translate-y-1/2"></div>
            <div class="absolute right-[-10%] top-[50%] -translate-y-1/2 p-2 rounded h-12 text-lg bg-gray-100 shadow" x-text="marginRight"></div>
        </div>
    </div>
</div>
