<div
    x-on:mouseup.window="firstPageHeaderStore.onMouseUp()"
    x-on:mousemove.window="
        firstPageHeaderStore.selectedElementId !== null && !firstPageHeaderStore.isResizeOrScaleActive
            ? firstPageHeaderStore.onMouseMove($event)
            : null
    "
    class="relative w-full box-border"
    :class="{'z-[100]': firstPageHeaderStore.snippetEditorXData !== null}"
>
    {{-- UI - first page header - height related --}}
    <div
        x-cloak
        x-show="printStore.editFirstPageHeader && firstPageHeaderStore.snippetEditorXData === null"
        class="absolute bottom-0 w-full border-t border-t-gray-200"
    ></div>
    <div
        x-cloak
        x-show="printStore.editFirstPageHeader && firstPageHeaderStore.snippetEditorXData === null"
        class="absolute top-0 w-full border-b border-b-gray-200"
    ></div>
    <div
        x-on:mousedown="firstPageHeaderStore.onMouseDownFirstPageHeader($event)"
        x-cloak
        x-show="printStore.editFirstPageHeader && firstPageHeaderStore.snippetEditorXData === null"
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
    {{-- UI size of a snippet --}}
    <div x-cloak x-show="firstPageHeaderStore.isSnippetResizeClicked && firstPageHeaderStore.selectedElementId !== null"
         :style="{'transform': `translate(${firstPageHeaderStore.selectedElementPos.x -50}px,${firstPageHeaderStore.selectedElementPos.y}px)` }"
         class="absolute left-0 top-0 z-[100] rounded shadow p-2 bg-gray-100">
        <div x-text="`h:${roundToOneDecimal(firstPageHeaderStore.selectedElementSize.height / firstPageHeaderStore.pxPerCm)}cm`"></div>
    </div>
    <div x-cloak x-show="firstPageHeaderStore.isSnippetResizeClicked && firstPageHeaderStore.selectedElementId !== null"
         :style="{'transform': `translate(${firstPageHeaderStore.selectedElementPos.x}px,${firstPageHeaderStore.selectedElementPos.y - 40}px)` }"
         class="absolute left-0 top-0 z-[100] rounded shadow p-2 bg-gray-100">
        <div x-text="`w: ${roundToOneDecimal(firstPageHeaderStore.selectedElementSize.width / firstPageHeaderStore.pyPerCm)}cm`"></div>
    </div>
    {{-- UI size of a snippet --}}
    {{-- UI snippet box name --}}
    <template x-for="(box,index) in firstPageHeaderStore.snippetNames" :key="`${box.ref.id}-${index}`">
        <div
            :style="{'transform': `translate(${box.ref.position.x}px,${box.ref.position.y - 15}px)` }"
            class="absolute left-0 top-0">
            <div class="text-gray-400 text-[12px]" x-text="box.name"></div>
        </div>
    </template>
    {{-- UI snippet box name --}}
    <div
        x-on:mouseup.window="firstPageHeaderStore.onMouseUpFirstPageHeader($event)"
        x-on:mousemove.window="
            firstPageHeaderStore.isFirstPageHeaderClicked
                ? firstPageHeaderStore.onMouseMoveFirstPageHeader($event)
                : null
        "
        x-ref="first-page-header"
        class="h-[7cm] box-border text-2xs leading-3"
        :style="`height: ${firstPageHeaderStore.height};`"
    >
        <div
            x-on:mousemove.window="firstPageHeaderStore.isImgResizeClicked ? firstPageHeaderStore.onMouseMoveScale($event) : firstPageHeaderStore.isSnippetResizeClicked ? firstPageHeaderStore.onMouseMoveResize($event) : null"
            class="w-0 h-0"></div>

    </div>
    <template
        id="{{ $client->id }}"
        x-ref="first-page-header-client-name"
    >
            <div
                id="first-page-header-client-name"
                data-type="container"
                draggable="false"
                class="absolute left-0 top-0"
                :class="{'bg-gray-300' : firstPageHeaderStore.selectedElementId === 'first-page-header-client-name'}"
                x-on:mousedown="printStore.editFirstPageHeader ?  firstPageHeaderStore.onMouseDown($event, 'first-page-header-client-name') : null"
            >
                <x-flux::print.elements.first-page-header-client-name :client="$client" />
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
            <x-flux::print.elements.first-page-header-address-one-line :client="$client" />
        </div>
    </template>
    <template
        id="{{ $client->id }}"
        x-ref="first-page-header-address"
    >
        <div
            id="first-page-header-address"
            data-type="container"
            draggable="false"
            class="absolute left-0 top-0"
            :class="{'bg-gray-300' : firstPageHeaderStore.selectedElementId === 'first-page-header-address'}"
            x-on:mousedown="printStore.editFirstPageHeader ?  firstPageHeaderStore.onMouseDown($event, 'first-page-header-address') : null"
        >
            <x-flux::print.elements.first-page-header-address :address="$address" />
        </div>
    </template>
    <template
        id="{{ $client->id }}"
        x-ref="first-page-header-subject"
    >
        <div
            id="first-page-header-subject"
            data-type="container"
            draggable="false"
            class="absolute left-0 top-0"
            :class="{'bg-gray-300' : firstPageHeaderStore.selectedElementId === 'first-page-header-subject'}"
            x-on:mousedown="printStore.editFirstPageHeader ?  firstPageHeaderStore.onMouseDown($event, 'first-page-header-subject') : null"
        >
            <x-flux::print.elements.first-page-header-subject :subject="$subject"/>
        </div>
    </template>
    <template
        id="{{ $client->id }}"
        x-ref="first-page-header-right-block"
    >
        <div
            id="first-page-header-right-block"
            data-type="container"
            draggable="false"
            class="absolute left-0 top-0 select-none"
            :class="{'bg-gray-300' : firstPageHeaderStore.selectedElementId === 'first-page-header-right-block'}"
            x-on:mousedown="printStore.editFirstPageHeader ?  firstPageHeaderStore.onMouseDown($event, 'first-page-header-right-block') : null"
        >
            <x-flux::print.elements.first-page-header-right-block-order :model="$model" />
        </div>
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
    <template
        id="{{ uniqid() }}"
        x-ref="first-page-header-additional-snippet"
    >
        <div
            x-data="temporarySnippetEditor(firstPageHeaderStore,$el.id)"
            x-init="onInit"
            x-on:mousedown="printStore.editFirstPageHeader && firstPageHeaderStore.snippetEditorXData === null ? firstPageHeaderStore.onMouseDown($event,$el.id,'temporary-snippet') : null"
            id="first-page-header-snippet-placeholder"
            data-type="resizable"
            draggable="false"
            class="absolute w-[10cm] h-[1.7cm] border text-2xs"
            :class="{
                    'border-primary-200': firstPageHeaderStore.isSnippetResizeClicked,
                    'bg-gray-100' : !firstPageHeaderStore.isResizeOrScaleActive && firstPageHeaderStore.selectedElementId === $el.id,
                    'z-[-10]': firstPageHeaderStore.snippetEditorXData !== null && firstPageHeaderStore.snippetEditorXData?.elementObj.id !== objId
                    }"
        >
            <div
                draggable="false"
                x-cloak x-show="printStore.editFirstPageHeader" class="relative w-full h-full">
                <x-icon
                    x-cloak
                    x-show="firstPageHeaderStore.snippetEditorXData === null"
                    dragable="false"
                    x-on:mousedown="toggleEditor()"
                    name="pencil" class="absolute cursor-pointer left-0 top-0 h-4 w-4 rounded-full text-left"></x-icon>
                <template x-if="firstPageHeaderStore.snippetEditorXData?.elementObj.id === objId">
                    <x-flux::editor
                        x-editable="firstPageHeaderStore.snippetEditorXData?.elementObj.id === objId"
                        class="absolute top-0 left-0 w-full p-0"
                        x-modelable="content"
                        x-model="text"
                        :full-height="true"
                        :tooltip-dropdown="true"
                        :default-font-size="9.1"
                        :line-height="true"
                        :text-background-colors="[]"
                        :show-editor-padding="false"
                        :text-align="true"
                        :transparent="true" />
                </template>
                <div
                    x-cloak
                    x-show="firstPageHeaderStore.snippetEditorXData === null"
                    class="text-left p-1"
                    x-html="text">
                </div>
                <x-icon
                    x-cloak
                    x-show="firstPageHeaderStore.snippetEditorXData === null"
                    dragable="false"
                    x-on:mousedown.stop="firstPageHeaderStore.onMouseDownResize($event, $el.parentElement.parentElement.id,'temporary-snippet')"
                    name="arrows-pointing-out" class="absolute cursor-pointer right-0 bottom-0 h-4 w-4 rounded-full"></x-icon>
            </div>
        </div>
    </template>
    <template
        id="{{ uniqid() }}"
        x-ref="first-page-header-snippet">
        <div
            x-data="snippetEditor(firstPageHeaderStore,$el.id)"
            x-init="onInit"
            id="first-page-header-snippet"
            x-on:mousedown="printStore.editFirstPageHeader && firstPageHeaderStore.snippetEditorXData === null ? firstPageHeaderStore.onMouseDown($event,$el.id,'snippet') : null"
            data-type="resizable"
            draggable="false"
            class="absolute w-[10cm] h-[1.7cm] border"
            :class="{
                    'border-primary-200': firstPageHeaderStore.isSnippetResizeClicked,
                    'bg-gray-100' : !firstPageHeaderStore.isResizeOrScaleActive && firstPageHeaderStore.selectedElementId === $el.id,
                    'z-[-10]': firstPageHeaderStore.snippetEditorXData !== null && firstPageHeaderStore.snippetEditorXData?.elementObj.id !== objId
                    }"
        >
            <div
                draggable="false"
                x-cloak x-show="printStore.editFirstPageHeader" class="relative w-full h-full">
                <x-icon
                    x-cloak
                    x-show="firstPageHeaderStore.snippetEditorXData === null"
                    dragable="false"
                    x-on:mousedown="toggleEditor()"
                    name="pencil" class="absolute cursor-pointer left-0 top-0 h-4 w-4 rounded-full text-left"></x-icon>
                <template x-if="firstPageHeaderStore.snippetEditorXData?.elementObj.id === objId">
                    <x-flux::editor
                        x-editable="firstPageHeaderStore.snippetEditorXData?.elementObj.id === objId"
                        class="absolute top-0 left-0 w-full p-0"
                        x-modelable="content"
                        x-model="text"
                        :full-height="true"
                        :text-align="true"
                        :default-font-size="9.1"
                        :line-height="true"
                        :text-background-colors="[]"
                        :tooltip-dropdown="true"
                        :show-editor-padding="false"
                        :transparent="true" />
                </template>
                <div
                    x-cloak
                    x-show="firstPageHeaderStore.snippetEditorXData === null"
                    x-html="text">
                </div>
                <x-icon
                    x-cloak
                    x-show="firstPageHeaderStore.snippetEditorXData === null"
                    dragable="false"
                    x-on:mousedown.stop="firstPageHeaderStore.onMouseDownResize($event, $el.parentElement.parentElement.id,'snippet')"
                    name="arrows-pointing-out" class="absolute cursor-pointer right-0 bottom-0 h-4 w-4 rounded-full"></x-icon>
            </div>
            <div
                x-cloak
                x-show="!printStore.editFirstPageHeader"
                x-html="text">
            </div>
        </div>
    </template>
</div>
