<footer
    class="relative w-full bg-white text-center"
    x-on:mouseup.window="footerStore.onMouseUp()"
    x-on:mousemove.window="
        footerStore.selectedElementId !== null && !footerStore.isResizeOrScaleActive
            ? footerStore.onMouseMove($event)
            : null
    "
>
    {{-- UI  footer height related --}}
    <div
        x-on:mousedown="footerStore.onMouseDownFooter($event)"
        x-cloak
        x-show="printStore.editFooter"
        class="absolute left-1/2 top-0 z-[100] h-6 w-6 -translate-x-1/2 -translate-y-1/2 cursor-pointer select-none rounded-full bg-flux-primary-400"
    >
        <div class="relative flex h-full w-full items-center justify-center">
            <div
                x-text="footerStore.footerHeight"
                class="absolute bottom-8 h-12 rounded bg-gray-100 p-2 text-lg shadow"
            ></div>
        </div>
    </div>
    {{-- UI  footer height related --}}
    {{-- UI position of a selected element --}}
    <div x-cloak x-show="!footerStore.isResizeOrScaleActive && footerStore.selectedElementId !== null"
         :style="{'transform': `translate(${footerStore.selectedElementPos.x -50}px,${footerStore.selectedElementPos.y}px)` }"
         class="absolute left-0 top-0 z-[100] rounded shadow p-2 bg-gray-100">
        <div x-text="`${roundToOneDecimal(footerStore.selectedElementPos.x / footerStore.pxPerCm)}cm`"></div>
    </div>
    <div x-cloak x-show="!footerStore.isResizeOrScaleActive && footerStore.selectedElementId !== null"
         :style="{'transform': `translate(${footerStore.selectedElementPos.x}px,${footerStore.selectedElementPos.y - 40}px)` }"
         class="absolute left-0 top-0 z-[100] rounded shadow p-2 bg-gray-100">
        <div x-text="`${roundToOneDecimal(footerStore.selectedElementPos.y / footerStore.pyPerCm)}cm`"></div>
    </div>
    {{-- UI position of a selected element --}}
    {{-- UI size of a snippet --}}
    <div x-cloak x-show="footerStore.isSnippetResizeClicked && footerStore.selectedElementId !== null"
         :style="{'transform': `translate(${footerStore.selectedElementPos.x -50}px,${footerStore.selectedElementPos.y}px)` }"
         class="absolute left-0 top-0 z-[100] rounded shadow p-2 bg-gray-100">
        <div x-text="`h:${roundToOneDecimal(footerStore.selectedElementSize.height / footerStore.pxPerCm)}cm`"></div>
    </div>
    <div x-cloak x-show="footerStore.isSnippetResizeClicked && footerStore.selectedElementId !== null"
         :style="{'transform': `translate(${footerStore.selectedElementPos.x}px,${footerStore.selectedElementPos.y - 40}px)` }"
         class="absolute left-0 top-0 z-[100] rounded shadow p-2 bg-gray-100">
        <div x-text="`w: ${roundToOneDecimal(footerStore.selectedElementSize.width / footerStore.pyPerCm)}cm`"></div>
    </div>
    {{-- UI size of a snippet --}}
    {{-- UI snippet box name --}}
    <template x-for="(box,index) in footerStore.snippetNames" :key="`${box.ref.id}-${index}`">
        <div
            :style="{'transform': `translate(${box.ref.position.x}px,${box.ref.position.y - 15}px)` }"
            class="absolute left-0 top-0">
            <div class="text-gray-400 text-[12px]" x-text="box.name"></div>
        </div>
    </template>
    {{-- UI snippet box name --}}
    <div
        x-ref="footer"
        class="footer-content relative h-full text-2xs leading-3"
        :style="`height: ${footerStore.footerHeight};`"
        x-on:mouseup.window="footerStore.onMouseUpFooter($event)"
        x-on:mousemove.window="footerStore.isFooterClicked ? footerStore.onMouseMoveFooter($event) : null"
    >
        <div
            x-on:mousemove.window="footerStore.isImgResizeClicked ? footerStore.onMouseMoveScale($event) : footerStore.isSnippetResizeClicked ? footerStore.onMouseMoveResize($event) : null"
            class="border-semi-black w-full border-t">
            <template
                id="{{ $client->id }}"
                x-ref="footer-client-{{ $client->id }}">
                <div
                    draggable="false"
                    data-type="container"
                    x-on:mousedown="printStore.editFooter ?  footerStore.onMouseDown($event,'footer-client-{{ $client->id }}') : null"
                    id="footer-client-{{ $client->id }}"
                    class="absolute left-0 top-0 w-fit cursor-pointer select-none text-left not-italic"
                    :class="{'bg-gray-300' : footerStore.selectedElementId === 'footer-client-{{ $client->id }}'}"
                >
                    <x-flux::print.elements.client :client="$client"/>
                </div>
            </template>
            <template
                id="{{ $client->id }}"
                x-ref="footer-logo">
                <div
                    id="footer-logo"
                    draggable="false"
                    data-type="resizable"
                    x-on:mousedown="printStore.editFooter ?  footerStore.onMouseDown($event, 'footer-logo') : null"
                    class="absolute left-0 top-0 h-[1.7cm] select-none"
                    :class="{'bg-gray-300' : !footerStore.isResizeOrScaleActive && footerStore.selectedElementId === 'footer-logo'}"
                >
                    <div
                        draggable="false"
                        x-cloak x-show="printStore.editFooter" class="relative w-full">
                        <x-icon
                            x-on:mousedown.stop="footerStore.onMouseDownScale($event, 'footer-logo')"
                            name="arrows-pointing-out" class="absolute cursor-pointer right-0 top-0 h-4 w-4 rounded-full"></x-icon>
                    </div>
                    <x-flux::print.elements.footer-logo :client="$client"/>
                </div>
            </template>
            @foreach ($client->bankConnections as $index => $bankConnection)
                <template
                    id="{{ uniqid() }}"
                    x-ref="footer-bank-{{ $bankConnection->id }}">
                    <div
                        id="footer-bank-{{ $bankConnection->id }}"
                        draggable="false"
                        data-type="container"
                        x-on:mousedown="printStore.editFooter ?  footerStore.onMouseDown($event,'footer-bank-{{ $bankConnection->id }}') : null"
                        class="absolute left-0 top-0 w-fit cursor-pointer select-none text-left"
                        :class="{'bg-gray-300' : footerStore.selectedElementId === 'footer-bank-{{ $bankConnection->id }}'}"
                    >
                        <x-flux::print.elements.bank-connection :bank-connection="$bankConnection"/>
                    </div>
                </template>
            @endforeach
            <template
                id="{{ uniqid() }}"
                x-ref="footer-additional-img"
            >
                <div
                    id="footer-img-placeholder"
                    x-on:mousedown="printStore.editFooter ?  footerStore.onMouseDown($event,$el.id,'temporary') : null"
                    data-type="resizable"
                    draggable="false"
                    class="absolute left-0 top-0 select-none h-[1.7cm]"
                    :class="{'bg-gray-300' : !footerStore.isResizeOrScaleActive && footerStore.selectedElementId === $el.id}"
                >
                    <div
                        draggable="false"
                        x-cloak x-show="printStore.editFooter" class="relative w-full">
                        <x-icon
                            x-on:mousedown.stop="footerStore.onMouseDownScale($event, $el.parentElement.parentElement.id,'temporary')"
                            name="arrows-pointing-out" class="absolute cursor-pointer right-0 top-0 h-4 w-4 rounded-full"></x-icon>
                    </div>
                <img
                    draggable="false"
                    class="max-h-full h-full w-full"
                    src=""
                     />
                </div>
            </template>
            <template
                id="{{ uniqid() }}"
                x-ref="footer-media">
                <div
                    id="footer-media"
                    x-on:mousedown="printStore.editFooter ?  footerStore.onMouseDown($event,$el.id,'media') : null"
                    data-type="resizable"
                    draggable="false"
                    class="absolute left-0 top-0 select-none h-[1.7cm]"
                    :class="{'bg-gray-300' : !footerStore.isResizeOrScaleActive && footerStore.selectedElementId === $el.id}"
                >
                    <div
                        draggable="false"
                        x-cloak x-show="printStore.editFooter" class="relative w-full">
                        <x-icon
                            x-on:mousedown.stop="footerStore.onMouseDownScale($event, $el.parentElement.parentElement.id,'media')"
                            name="arrows-pointing-out" class="absolute cursor-pointer right-0 top-0 h-4 w-4 rounded-full"></x-icon>
                    </div>
                    <img
                        draggable="false"
                        class="max-h-full h-full w-full"
                        src=""
                    />
                </div>
            </template>
            <template
                id="{{ uniqid() }}"
                x-ref="footer-additional-snippet"
            >
                <div
                    x-data="temporarySnippetEditor(footerStore,$el.id)"
                    x-init="onInit"
                    x-on:mousedown="printStore.editFooter && footerStore.snippetEditorXData === null ? footerStore.onMouseDown($event,$el.id,'temporary-snippet') : null"
                    id="footer-snippet-placeholder"
                    data-type="resizable"
                    draggable="false"
                    class="absolute w-[10cm] h-[1.7cm] border"
                    :class="{
                    'border-primary-200': footerStore.isSnippetResizeClicked,
                    'bg-gray-100' : !footerStore.isResizeOrScaleActive && footerStore.selectedElementId === $el.id
                    }"
                >
                    <div
                        draggable="false"
                        x-cloak x-show="printStore.editFooter" class="relative w-full h-full">
                        <x-icon
                            x-cloak
                            x-show="footerStore.snippetEditorXData === null"
                            dragable="false"
                            x-on:mousedown="toggleEditor()"
                            name="pencil" class="absolute cursor-pointer right-0 top-0 h-4 w-4 rounded-full text-left"></x-icon>
                        <template x-if="footerStore.snippetEditorXData?.elementObj.id === objId">
                            <x-flux::editor
                                x-editable="footerStore.snippetEditorXData?.elementObj.id === objId"
                                class="absolute top-0 left-0 w-full p-0"
                                x-modelable="content"
                                x-model="text"
                                :full-height="true"
                                :tooltip-dropdown="true"
                                :transparent="true" />
                        </template>
                        <div
                            x-cloak
                            x-show="footerStore.snippetEditorXData === null"
                            class="text-left text-[12px] p-1"
                            x-html="text">
                        </div>
                        <x-icon
                            x-cloak
                            x-show="footerStore.snippetEditorXData === null"
                            dragable="false"
                            x-on:mousedown.stop="footerStore.onMouseDownResize($event, $el.parentElement.parentElement.id,'temporary-snippet')"
                            name="arrows-pointing-out" class="absolute cursor-pointer right-0 bottom-0 h-4 w-4 rounded-full"></x-icon>
                    </div>
                </div>
            </template>
            <template
                id="{{ uniqid() }}"
                x-ref="footer-snippet">
                <div
                    x-data="snippetEditor(footerStore,$el.id)"
                    x-init="onInit"
                    id="footer-snippet"
                    x-on:mousedown="printStore.editFooter && footerStore.snippetEditorXData === null ? footerStore.onMouseDown($event,$el.id,'snippet') : null"
                    data-type="resizable"
                    draggable="false"
                    class="absolute w-[10cm] h-[1.7cm] border"
                    :class="{
                    'border-primary-200': footerStore.isSnippetResizeClicked,
                    'bg-gray-100' : !footerStore.isResizeOrScaleActive && footerStore.selectedElementId === $el.id
                    }"
                >
                    <div
                        draggable="false"
                        x-cloak x-show="printStore.editFooter" class="relative w-full h-full">
                        <x-icon
                            x-cloak
                            x-show="footerStore.snippetEditorXData === null"
                            dragable="false"
                            x-on:mousedown="toggleEditor()"
                            name="pencil" class="absolute cursor-pointer right-0 top-0 h-4 w-4 rounded-full text-left"></x-icon>
                        <template x-if="footerStore.snippetEditorXData?.elementObj.id === objId">
                            <x-flux::editor
                                x-editable="footerStore.snippetEditorXData?.elementObj.id === objId"
                                class="absolute top-0 left-0 w-full p-0"
                                x-modelable="content"
                                x-model="text"
                                :full-height="true"
                                :tooltip-dropdown="true"
                                :transparent="true" />
                        </template>
                        <div
                            x-cloak
                            x-show="footerStore.snippetEditorXData === null"
                            class="text-left text-[12px] p-1"
                            x-html="text">
                        </div>
                        <x-icon
                            x-cloak
                            x-show="footerStore.snippetEditorXData === null"
                            dragable="false"
                            x-on:mousedown.stop="footerStore.onMouseDownResize($event, $el.parentElement.parentElement.id,'snippet')"
                            name="arrows-pointing-out" class="absolute cursor-pointer right-0 bottom-0 h-4 w-4 rounded-full"></x-icon>
                    </div>
                    <div
                        x-cloak
                        x-show="!printStore.editFooter"
                        class="text-left text-[12px] p-1"
                        x-html="text">
                    </div>
                </div>
            </template>
            <div class="clear-both"></div>
        </div>
    </div>
</footer>
