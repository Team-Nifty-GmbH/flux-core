<footer
    class="relative w-full bg-white text-center"
    x-on:mouseup.window="footerStore.onMouseUp()"
    x-on:mousemove.window="
        footerStore.selectedElementId !== null && ! footerStore.isResizeOrScaleActive
            ? footerStore.onMouseMove($event)
            : null
    "
>
    {{-- UI  footer height related --}}
    <div
        x-on:mousedown="footerStore.onMouseDownFooter($event)"
        x-cloak
        x-show="printStore.editFooter && footerStore.snippetEditorXData === null"
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
    <div
        x-cloak
        x-show="!footerStore.isResizeOrScaleActive && footerStore.selectedElementId !== null"
        x-bind:style="{
            'transform': `translate(${footerStore.selectedElementPos.x - 50}px,${footerStore.selectedElementPos.y}px)`,
        }"
        class="absolute left-0 top-0 z-[100] rounded bg-gray-100 p-2 shadow"
    >
        <div
            x-text="`${roundToOneDecimal(footerStore.selectedElementPos.x / footerStore.pxPerCm)}cm`"
        ></div>
    </div>
    <div
        x-cloak
        x-show="!footerStore.isResizeOrScaleActive && footerStore.selectedElementId !== null"
        x-bind:style="{
            'transform': `translate(${footerStore.selectedElementPos.x}px,${footerStore.selectedElementPos.y - 40}px)`,
        }"
        class="absolute left-0 top-0 z-[100] rounded bg-gray-100 p-2 shadow"
    >
        <div
            x-text="`${roundToOneDecimal(footerStore.selectedElementPos.y / footerStore.pyPerCm)}cm`"
        ></div>
    </div>
    {{-- UI position of a selected element --}}
    {{-- UI size of a snippet --}}
    <div
        x-cloak
        x-show="footerStore.isSnippetResizeClicked && footerStore.selectedElementId !== null"
        x-bind:style="{
            'transform': `translate(${footerStore.selectedElementPos.x - 50}px,${footerStore.selectedElementPos.y}px)`,
        }"
        class="absolute left-0 top-0 z-[100] rounded bg-gray-100 p-2 shadow"
    >
        <div
            x-text="`h:${roundToOneDecimal(footerStore.selectedElementSize.height / footerStore.pxPerCm)}cm`"
        ></div>
    </div>
    <div
        x-cloak
        x-show="footerStore.isSnippetResizeClicked && footerStore.selectedElementId !== null"
        x-bind:style="{
            'transform': `translate(${footerStore.selectedElementPos.x}px,${footerStore.selectedElementPos.y - 40}px)`,
        }"
        class="absolute left-0 top-0 z-[100] rounded bg-gray-100 p-2 shadow"
    >
        <div
            x-text="`w: ${roundToOneDecimal(footerStore.selectedElementSize.width / footerStore.pyPerCm)}cm`"
        ></div>
    </div>
    {{-- UI size of a snippet --}}
    {{-- UI snippet box name --}}
    <template
        x-for="(box, index) in footerStore.snippetNames"
        :key="`${box.ref.id}-${index}`"
    >
        <div
            x-bind:style="{
                'transform': `translate(${box.ref.position.x}px,${box.ref.position.y - 15}px)`,
            }"
            class="absolute left-0 top-0"
        >
            <div class="text-[12px] text-gray-400" x-text="box.name"></div>
        </div>
    </template>
    {{-- UI snippet box name --}}
    <div
        x-ref="footer"
        class="footer-content relative h-full text-2xs leading-3"
        x-bind:style="`height: ${footerStore.footerHeight};`"
        x-on:mouseup.window="footerStore.onMouseUpFooter($event)"
        x-on:mousemove.window="footerStore.isFooterClicked ? footerStore.onMouseMoveFooter($event) : null"
    >
        <div
            x-on:mousemove.window="
                footerStore.isImgResizeClicked
                    ? footerStore.onMouseMoveScale($event)
                    : footerStore.isSnippetResizeClicked
                      ? footerStore.onMouseMoveResize($event)
                      : null
            "
            class="border-semi-black w-full border-t"
        >
            <template
                id="{{ data_get($client, 'id', uniqid()) }}"
                x-ref="footer-client-{{ data_get($client, 'id', uniqid()) }}"
            >
                <div
                    draggable="false"
                    data-type="container"
                    x-on:mousedown="printStore.editFooter ?  footerStore.onMouseDown($event,'footer-client-{{ data_get($client,'id',uniqid()) }}') : null"
                    id="footer-client-{{ $client->id }}"
                    class="absolute left-0 top-0 w-fit cursor-pointer select-none text-left not-italic"
                    x-bind:class="{'bg-gray-300' : footerStore.selectedElementId === 'footer-client-{{ data_get($client,'id',uniqid()) }}'}"
                >
                    <x-flux::print.elements.client :client="$client" />
                </div>
            </template>
            <template
                id="{{ data_get($client, 'id', uniqid()) }}"
                x-ref="footer-logo"
            >
                <div
                    id="footer-logo"
                    draggable="false"
                    data-type="resizable"
                    x-on:mousedown="printStore.editFooter ? footerStore.onMouseDown($event, 'footer-logo') : null"
                    class="absolute left-0 top-0 h-[1.7cm] select-none"
                    x-bind:class="{
                        'bg-gray-300':
                            ! footerStore.isResizeOrScaleActive &&
                            footerStore.selectedElementId === 'footer-logo',
                    }"
                >
                    <div
                        draggable="false"
                        x-cloak
                        x-show="printStore.editFooter"
                        class="relative w-full"
                    >
                        <x-icon
                            x-on:mousedown.stop="footerStore.onMouseDownScale($event, 'footer-logo')"
                            name="arrows-pointing-out"
                            class="absolute right-0 top-0 h-4 w-4 cursor-pointer rounded-full"
                        ></x-icon>
                    </div>
                    <x-flux::print.elements.footer-logo :client="$client" />
                </div>
            </template>
            @foreach (data_get($client, 'bankConnections', []) as $index => $bankConnection)
                <template
                    id="{{ uniqid() }}"
                    x-ref="footer-bank-{{ data_get($bankConnection, 'id', uniqid()) }}"
                >
                    <div
                        id="footer-bank-{{ data_get($bankConnection, 'id', uniqid()) }}"
                        draggable="false"
                        data-type="container"
                        x-on:mousedown="printStore.editFooter ?  footerStore.onMouseDown($event,'footer-bank-{{ data_get($bankConnection,'id',uniqid()) }}') : null"
                        class="absolute left-0 top-0 w-fit cursor-pointer select-none text-left"
                        x-bind:class="{'bg-gray-300' : footerStore.selectedElementId === 'footer-bank-{{ data_get($bankConnection,'id',uniqid()) }}'}"
                    >
                        <x-flux::print.elements.bank-connection
                            :bank-connection="$bankConnection"
                        />
                    </div>
                </template>
            @endforeach

            <template id="{{ uniqid() }}" x-ref="footer-additional-img">
                <div
                    id="footer-img-placeholder"
                    x-on:mousedown="
                        printStore.editFooter
                            ? footerStore.onMouseDown($event, $el.id, 'temporary')
                            : null
                    "
                    data-type="resizable"
                    draggable="false"
                    class="absolute left-0 top-0 h-[1.7cm] select-none"
                    x-bind:class="{
                        'bg-gray-300':
                            ! footerStore.isResizeOrScaleActive &&
                            footerStore.selectedElementId === $el.id,
                    }"
                >
                    <div
                        draggable="false"
                        x-cloak
                        x-show="printStore.editFooter"
                        class="relative w-full"
                    >
                        <x-icon
                            x-on:mousedown.stop="footerStore.onMouseDownScale($event, $el.parentElement.parentElement.id,'temporary')"
                            name="arrows-pointing-out"
                            class="absolute right-0 top-0 h-4 w-4 cursor-pointer rounded-full"
                        ></x-icon>
                    </div>
                    <img
                        draggable="false"
                        class="h-full max-h-full w-full"
                        src=""
                    />
                </div>
            </template>
            <template id="{{ uniqid() }}" x-ref="footer-media">
                <div
                    id="footer-media"
                    x-on:mousedown="printStore.editFooter ? footerStore.onMouseDown($event, $el.id, 'media') : null"
                    data-type="resizable"
                    draggable="false"
                    class="absolute left-0 top-0 h-[1.7cm] select-none"
                    x-bind:class="{
                        'bg-gray-300':
                            ! footerStore.isResizeOrScaleActive &&
                            footerStore.selectedElementId === $el.id,
                    }"
                >
                    <div
                        draggable="false"
                        x-cloak
                        x-show="printStore.editFooter"
                        class="relative w-full"
                    >
                        <x-icon
                            x-on:mousedown.stop="footerStore.onMouseDownScale($event, $el.parentElement.parentElement.id,'media')"
                            name="arrows-pointing-out"
                            class="absolute right-0 top-0 h-4 w-4 cursor-pointer rounded-full"
                        ></x-icon>
                    </div>
                    <img
                        draggable="false"
                        class="h-full max-h-full w-full"
                        src=""
                    />
                </div>
            </template>
            <template id="{{ uniqid() }}" x-ref="footer-additional-snippet">
                <div
                    x-data="temporarySnippetEditor(footerStore, $el.id)"
                    x-init="onInit"
                    x-on:mousedown="
                        printStore.editFooter && footerStore.snippetEditorXData === null
                            ? footerStore.onMouseDown($event, $el.id, 'temporary-snippet')
                            : null
                    "
                    id="footer-snippet-placeholder"
                    data-type="resizable"
                    draggable="false"
                    class="absolute h-[1.7cm] w-[10cm] border"
                    x-bind:class="{
                        'border-primary-200': footerStore.isSnippetResizeClicked,
                        'bg-gray-100':
                            ! footerStore.isResizeOrScaleActive &&
                            footerStore.selectedElementId === $el.id,
                        'z-[-10]':
                            footerStore.snippetEditorXData !== null &&
                            footerStore.snippetEditorXData?.elementObj.id !== objId,
                    }"
                >
                    <div
                        draggable="false"
                        x-cloak
                        x-show="printStore.editFooter"
                        class="relative h-full w-full"
                    >
                        <x-icon
                            x-cloak
                            x-show="footerStore.snippetEditorXData === null"
                            dragable="false"
                            x-on:mousedown="toggleEditor()"
                            name="pencil"
                            class="absolute left-0 top-0 h-4 w-4 cursor-pointer rounded-full text-left"
                        ></x-icon>
                        <template
                            x-if="footerStore.snippetEditorXData?.elementObj.id === objId"
                        >
                            <x-flux::editor
                                x-editable="footerStore.snippetEditorXData?.elementObj.id === objId"
                                class="absolute left-0 top-0 w-full p-0"
                                x-modelable="content"
                                x-model="text"
                                :full-height="true"
                                :tooltip-dropdown="true"
                                :show-editor-padding="false"
                                :default-font-size="9.1"
                                :line-height="true"
                                :text-align="true"
                                :text-background-colors="[]"
                                :transparent="true"
                            />
                        </template>
                        <div
                            x-cloak
                            x-show="footerStore.snippetEditorXData === null"
                            class="p-1 text-left"
                            x-html="text"
                        ></div>
                        <x-icon
                            x-cloak
                            x-show="footerStore.snippetEditorXData === null"
                            dragable="false"
                            x-on:mousedown.stop="footerStore.onMouseDownResize($event, $el.parentElement.parentElement.id,'temporary-snippet')"
                            name="arrows-pointing-out"
                            class="absolute bottom-0 right-0 h-4 w-4 cursor-pointer rounded-full"
                        ></x-icon>
                    </div>
                </div>
            </template>
            <template id="{{ uniqid() }}" x-ref="footer-snippet">
                <div
                    x-data="snippetEditor(footerStore, $el.id)"
                    x-init="onInit"
                    id="footer-snippet"
                    x-on:mousedown="
                        printStore.editFooter && footerStore.snippetEditorXData === null
                            ? footerStore.onMouseDown($event, $el.id, 'snippet')
                            : null
                    "
                    data-type="resizable"
                    draggable="false"
                    class="absolute h-[1.7cm] w-[10cm] border"
                    x-bind:class="{
                        'border-primary-200': footerStore.isSnippetResizeClicked,
                        'bg-gray-100':
                            ! footerStore.isResizeOrScaleActive &&
                            footerStore.selectedElementId === $el.id,
                        'z-[-10]':
                            footerStore.snippetEditorXData !== null &&
                            footerStore.snippetEditorXData?.elementObj.id !== objId,
                    }"
                >
                    <div
                        draggable="false"
                        x-cloak
                        x-show="printStore.editFooter"
                        class="h-full w-full"
                    >
                        <x-icon
                            x-cloak
                            x-show="footerStore.snippetEditorXData === null"
                            dragable="false"
                            x-on:mousedown="toggleEditor()"
                            name="pencil"
                            class="absolute left-0 top-0 h-4 w-4 cursor-pointer rounded-full text-left"
                        ></x-icon>
                        <template
                            x-if="footerStore.snippetEditorXData?.elementObj.id === objId"
                        >
                            <x-flux::editor
                                x-editable="footerStore.snippetEditorXData?.elementObj.id === objId"
                                class="absolute left-0 top-0 w-full p-0"
                                x-modelable="content"
                                x-model="text"
                                :full-height="true"
                                :text-align="true"
                                :tooltip-dropdown="true"
                                :text-background-colors="[]"
                                :line-height="true"
                                :default-font-size="9.1"
                                :show-editor-padding="false"
                                :transparent="true"
                            />
                        </template>
                        <div
                            x-cloak
                            x-show="footerStore.snippetEditorXData === null"
                            x-html="text"
                        ></div>
                        <x-icon
                            x-cloak
                            x-show="footerStore.snippetEditorXData === null"
                            dragable="false"
                            x-on:mousedown.stop="footerStore.onMouseDownResize($event, $el.parentElement.parentElement.id,'snippet')"
                            name="arrows-pointing-out"
                            class="absolute bottom-0 right-0 h-4 w-4 cursor-pointer rounded-full"
                        ></x-icon>
                    </div>
                    <div
                        x-cloak
                        x-show="!printStore.editFooter"
                        x-html="text"
                    ></div>
                </div>
            </template>
            <div class="clear-both"></div>
        </div>
    </div>
</footer>
