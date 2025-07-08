<div x-data="printEditorMain()"
     class="flex h-[29.7cm] items-center space-x-4">
    <div class="h-full w-[300px] rounded bg-white shadow"></div>
    <div
        x-init="onInit"
        class="h-[29.7cm] min-h-[29.7cm] w-[21cm] min-w-[21cm] bg-white shadow"
    >
        <div
            x-on:mousemove.window="isAnyClicked ? onMouseMove($event) : null"
            x-on:mouseup.window="onMouseUp($event)"
            :class="{'bg-flux-primary-300': editMargin, 'bg-gray-200': !editMargin}"
            class="h-full w-full"
            :style="{'padding-left': marginLeft, 'padding-right': marginRight, 'padding-top': marginTop, 'padding-bottom': marginBottom}"
        >
            <div class="relative h-full w-full bg-white">
                {{-- scale for converting cm to px --}}
                <div
                    x-ref="scale"
                    class="absolute left-0 top-0 h-[1cm] w-[1cm]"
                ></div>
                {{-- content --}}
                <div class="h-full flex flex-col ">
                    <x-flux::print.header :client="$this->clientFluent" :subject="$subject"/>
                    <div class="flex-1">
{{--                        {!! $this->orderPrint() !!}--}}
                    </div>
                    <x-flux::print.footer :client="$this->clientFluent"/>
                </div>
                {{-- content --}}
                <div
                    x-cloak
                    x-show="editMargin"
                    x-on:mousedown="onMouseDown($event, 'margin-top')"
                    :class="{'bg-flux-primary-500': isTopClicked, 'bg-flux-primary-700': !isTopClicked}"
                    draggable="false"
                    class="absolute left-1/2 top-0 h-6 w-6 -translate-x-1/2 -translate-y-1/2 cursor-pointer select-none rounded-full shadow"
                >
                    <div
                        class="relative flex h-full w-full items-center justify-center"
                    >
                        <div
                            class="absolute left-10 h-12 rounded bg-gray-100 p-2 text-lg shadow"
                            x-text="marginTop"
                        ></div>
                    </div>
                </div>
                <div
                    x-cloak
                    x-show="editMargin"
                    x-on:mousedown="onMouseDown($event, 'margin-left')"
                    :class="{'bg-flux-primary-500': isLeftClicked, 'bg-flux-primary-700': !isLeftClicked}"
                    draggable="false"
                    class="absolute left-0 top-1/2 h-6 w-6 -translate-x-1/2 -translate-y-1/2 cursor-pointer select-none rounded-full bg-flux-primary-700"
                >
                    <div
                        class="relative flex h-full w-full items-center justify-center"
                    >
                        <div
                            class="absolute right-10 h-12 rounded bg-gray-100 p-2 text-lg shadow"
                            x-text="marginLeft"
                        ></div>
                    </div>
                </div>
                <div
                    x-cloak
                    x-show="editMargin"
                    x-on:mousedown="onMouseDown($event, 'margin-bottom')"
                    :class="{'bg-flux-primary-500': isBottomClicked, 'bg-flux-primary-700': !isBottomClicked}"
                    class="absolute bottom-0 left-1/2 h-6 w-6 -translate-x-1/2 translate-y-1/2 cursor-pointer select-none rounded-full"
                >
                    <div
                        class="relative flex h-full w-full items-center justify-center"
                    >
                        <div
                            class="absolute left-10 h-12 rounded bg-gray-100 p-2 text-lg shadow"
                            x-text="marginBottom"
                        ></div>
                    </div>
                </div>

                <div
                    x-cloak
                    x-show="editMargin"
                    x-on:mousedown="onMouseDown($event, 'margin-right')"
                    draggable="false"
                    :class="{'bg-flux-primary-500': isRightClicked, 'bg-flux-primary-700': !isRightClicked}"
                    class="absolute right-0 top-1/2 h-6 w-6 -translate-y-1/2 translate-x-1/2 cursor-pointer select-none rounded-full"
                >
                    <div
                        class="relative flex h-full w-full items-center justify-center"
                    >
                        <div
                            class="absolute left-10 h-12 rounded bg-gray-100 p-2 text-lg shadow"
                            x-text="marginRight"
                        ></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div
        class="h-full w-[300px] rounded p-4 bg-white shadow">
        <div
            x-cloak
            x-show="!anyEdit"
            class="flex flex-col space-y-4">
            <x-button
                x-on:click="toggleEditMargin"
                text="Edit Margin"/>
            <x-button
                x-on:click="toggleEditHeader"
                text="Edit Header"/>
            <x-button
                x-on:click="toggleEditFooter"
                text="Edit Footer"/>
        </div>
        <div
            x-cloak
            x-show="anyEdit"
            class="h-full flex flex-col justify-end">
            <div class="flex items-center justify-between">
                <x-button
                    x-on:click="closeEditor"
                    text="Cancel"
                />
                <x-button
                    text="Save"
                />
            </div>
        </div>
    </div>
</div>
