<div
    class="h-[29.7cm] min-h-[29.7cm] w-[21cm] min-w-[21cm] bg-white shadow"
>
    <div
        x-on:mousemove.window="printStore.isAnyClicked ? printStore.onMouseMove($event) : null"
        x-on:mouseup.window="printStore.onMouseUp($event)"
        :class="{'bg-flux-primary-300': printStore.editMargin, 'bg-gray-200': !printStore.editMargin}"
        class="h-full w-full"
        :style="{'padding-left': printStore.marginLeft, 'padding-right': printStore.marginRight, 'padding-top': printStore.marginTop, 'padding-bottom': printStore.marginBottom}"
    >
        <div class="relative h-full w-full bg-white">
            {{-- scale for converting cm to px --}}
            <div
                x-ref="scale"
                class="absolute left-0 top-0 h-[1cm] w-[1cm]"
            ></div>
            {{-- content --}}
            <div class="flex h-full flex-col">
                <x-flux::print.header
                    :client="$this->client"
                    :subject="$this->subject"
                />
                <div class="flex-1">
                    {{-- {!! $this->orderPrint() !!} --}}
                </div>
                <x-flux::print.footer :client="$this->client" />
            </div>
            {{-- content --}}
            <div
                x-cloak
                x-show="printStore.editMargin"
                x-on:mousedown="printStore.onMouseDown($event, 'margin-top')"
                :class="{'bg-flux-primary-500': printStore.isTopClicked, 'bg-flux-primary-700': !printStore.isTopClicked}"
                draggable="false"
                class="absolute left-1/2 top-0 h-6 w-6 -translate-x-1/2 -translate-y-1/2 cursor-pointer select-none rounded-full shadow"
            >
                <div
                    class="relative flex h-full w-full items-center justify-center"
                >
                    <div
                        class="absolute left-10 h-12 rounded bg-gray-100 p-2 text-lg shadow"
                        x-text="printStore.marginTop"
                    ></div>
                </div>
            </div>
            <div
                x-cloak
                x-show="printStore.editMargin"
                x-on:mousedown="printStore.onMouseDown($event, 'margin-left')"
                :class="{'bg-flux-primary-500': printStore.isLeftClicked, 'bg-flux-primary-700': !printStore.isLeftClicked}"
                draggable="false"
                class="absolute left-0 top-1/2 h-6 w-6 -translate-x-1/2 -translate-y-1/2 cursor-pointer select-none rounded-full bg-flux-primary-700"
            >
                <div
                    class="relative flex h-full w-full items-center justify-center"
                >
                    <div
                        class="absolute right-10 h-12 rounded bg-gray-100 p-2 text-lg shadow"
                        x-text="printStore.marginLeft"
                    ></div>
                </div>
            </div>
            <div
                x-cloak
                x-show="printStore.editMargin"
                x-on:mousedown="printStore.onMouseDown($event, 'margin-bottom')"
                :class="{'bg-flux-primary-500': printStore.isBottomClicked, 'bg-flux-primary-700': !printStore.isBottomClicked}"
                class="absolute bottom-0 left-1/2 h-6 w-6 -translate-x-1/2 translate-y-1/2 cursor-pointer select-none rounded-full"
            >
                <div
                    class="relative flex h-full w-full items-center justify-center"
                >
                    <div
                        class="absolute left-10 h-12 rounded bg-gray-100 p-2 text-lg shadow"
                        x-text="printStore.marginBottom"
                    ></div>
                </div>
            </div>

            <div
                x-cloak
                x-show="printStore.editMargin"
                x-on:mousedown="printStore.onMouseDown($event, 'margin-right')"
                draggable="false"
                :class="{'bg-flux-primary-500': printStore.isRightClicked, 'bg-flux-primary-700': !printStore.isRightClicked}"
                class="absolute right-0 top-1/2 h-6 w-6 -translate-y-1/2 translate-x-1/2 cursor-pointer select-none rounded-full"
            >
                <div
                    class="relative flex h-full w-full items-center justify-center"
                >
                    <div
                        class="absolute left-10 h-12 rounded bg-gray-100 p-2 text-lg shadow"
                        x-text="printStore.marginRight"
                    ></div>
                </div>
            </div>
        </div>
    </div>
</div>
