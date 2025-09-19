{{-- TODO: add page count position as a default element --}}
<header
    class="h-[1.7cm] w-full bg-white text-center relative"
    x-on:mouseup.window="headerStore.onMouseUp()"
    x-on:mousemove.window="
        headerStore.selectedElementId !== null && !headerStore.isResizeOrScaleActive
            ? headerStore.onMouseMove($event)
            : null
    "
    :style="`height: ${headerStore.headerHeight};`"
>
    {{-- UI  header height related --}}
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
    {{-- UI size of a snippet --}}
    <div x-cloak x-show="headerStore.isSnippetResizeClicked && headerStore.selectedElementId !== null"
         :style="{'transform': `translate(${headerStore.selectedElementPos.x -50}px,${headerStore.selectedElementPos.y}px)` }"
         class="absolute left-0 top-0 z-[100] rounded shadow p-2 bg-gray-100">
        <div x-text="`h:${roundToOneDecimal(headerStore.selectedElementSize.height / headerStore.pxPerCm)}cm`"></div>
    </div>
    <div x-cloak x-show="headerStore.isSnippetResizeClicked && headerStore.selectedElementId !== null"
         :style="{'transform': `translate(${headerStore.selectedElementPos.x}px,${headerStore.selectedElementPos.y - 40}px)` }"
         class="absolute left-0 top-0 z-[100] rounded shadow p-2 bg-gray-100">
        <div x-text="`w: ${roundToOneDecimal(headerStore.selectedElementSize.width / headerStore.pyPerCm)}cm`"></div>
    </div>
    {{-- UI size of a snippet --}}
    {{-- UI snippet box name --}}
    <template x-for="(box,index) in headerStore.snippetNames" :key="`${box.ref.id}-${index}`">
        <div
            :style="{'transform': `translate(${box.ref.position.x}px,${box.ref.position.y - 15}px)` }"
            class="absolute left-0 top-0">
            <div class="text-gray-400 text-[12px]" x-text="box.name"></div>
        </div>
    </template>
    {{-- UI snippet box name --}}
    <div
        x-ref="header"
        x-on:mouseup.window="headerStore.onMouseUpHeader($event)"
        x-on:mousemove.window="headerStore.isHeaderClicked ? headerStore.onMouseMoveHeader($event) : null"
        class="header-content relative h-full w-full">
    </div>
    <div
        x-on:mousemove.window="headerStore.isImgResizeClicked ? headerStore.onMouseMoveScale($event) : headerStore.isSnippetResizeClicked ? headerStore.onMouseMoveResize($event) : null"
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
            data-type="resizable"
            x-on:mousedown="printStore.editHeader ?  headerStore.onMouseDown($event, 'header-logo') : null"
            class="absolute left-0 top-0 h-[1.7cm] select-none"
            :class="{'bg-gray-300' : !headerStore.isImgResizeClicked && headerStore.selectedElementId === 'header-logo'}"
        >
            <div
                draggable="false"
                x-cloak x-show="printStore.editHeader" class="relative w-full">
                <x-icon
                    x-on:mousedown.stop="headerStore.onMouseDownScale($event, 'header-logo')"
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
    <template id="{{ uniqid() }}"
              x-ref="header-page-count"
    >
        <div
            id="header-page-count"
            data-type="container"
            draggable="false"
            x-on:mousedown="printStore.editHeader ?  headerStore.onMouseDown($event, 'header-page-count') : null"
            class="absolute left-0 top-0 w-fit select-none"
            :class="{'bg-gray-300' : !headerStore.isImgResizeClicked && headerStore.selectedElementId === 'header-page-count'}"
        >
            <div class="text-xs">Page 1 of 1</div>
        </div>
    </template>
    <template
        id="{{ uniqid() }}"
        x-ref="header-additional-img"
    >
        <div
            id="header-img-placeholder"
            x-on:mousedown="printStore.editHeader ?  headerStore.onMouseDown($event,$el.id,'temporary') : null"
            data-type="resizable"
            draggable="false"
            class="absolute left-0 top-0 select-none h-[1.7cm]"
            :class="{'bg-gray-300' : !headerStore.isImgResizeClicked && headerStore.selectedElementId === $el.id}"
        >
            <div
                draggable="false"
                x-cloak x-show="printStore.editHeader" class="relative w-full">
                <x-icon
                    x-on:mousedown.stop="headerStore.onMouseDownScale($event, $el.parentElement.parentElement.id,'temporary')"
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
        x-ref="header-media">
        <div
            id="header-media"
            x-on:mousedown="printStore.editHeader ?  headerStore.onMouseDown($event,$el.id,'media') : null"
            data-type="resizable"
            draggable="false"
            class="absolute left-0 top-0 select-none h-[1.7cm]"
            :class="{'bg-gray-300' : !headerStore.isImgResizeClicked && headerStore.selectedElementId === $el.id}"
        >
            <div
                draggable="false"
                x-cloak x-show="printStore.editHeader" class="relative w-full">
                <x-icon
                    x-on:mousedown.stop="headerStore.onMouseDownScale($event, $el.parentElement.parentElement.id,'media')"
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
        x-ref="header-additional-snippet"
    >
        <div
            x-data="temporarySnippetEditor(headerStore,$el.id)"
            x-init="onInit"
            x-on:mousedown="printStore.editHeader && headerStore.snippetEditorXData === null ? headerStore.onMouseDown($event,$el.id,'temporary-snippet') : null"
            id="header-snippet-placeholder"
            data-type="resizable"
            draggable="false"
            class="absolute w-[10cm] h-[1.7cm] border"
            :class="{
                    'border-primary-200': headerStore.isSnippetResizeClicked,
                    'bg-gray-100' : !headerStore.isResizeOrScaleActive && headerStore.selectedElementId === $el.id
                    }"
        >
            <div
                draggable="false"
                x-cloak x-show="printStore.editHeader" class="relative w-full h-full">
                <x-icon
                    x-cloak
                    x-show="headerStore.snippetEditorXData === null"
                    dragable="false"
                    x-on:mousedown="toggleEditor()"
                    name="pencil" class="absolute cursor-pointer left-0 top-0 h-4 w-4 rounded-full text-left"></x-icon>
                <template x-if="headerStore.snippetEditorXData?.elementObj.id === objId">
                    <x-flux::editor
                        x-editable="headerStore.snippetEditorXData?.elementObj.id === objId"
                        class="absolute top-0 left-0 w-full p-0"
                        x-modelable="content"
                        x-model="text"
                        :full-height="true"
                        :tooltip-dropdown="true"
                        :show-editor-padding="false"
                        :text-align="true"
                        :transparent="true" />
                </template>
                <div
                    x-cloak
                    x-show="headerStore.snippetEditorXData === null"
                    class="text-left p-1"
                    x-html="text">
                </div>
                <x-icon
                    x-cloak
                    x-show="headerStore.snippetEditorXData === null"
                    dragable="false"
                    x-on:mousedown.stop="headerStore.onMouseDownResize($event, $el.parentElement.parentElement.id,'temporary-snippet')"
                    name="arrows-pointing-out" class="absolute cursor-pointer right-0 bottom-0 h-4 w-4 rounded-full"></x-icon>
            </div>
        </div>
    </template>
    <template
        id="{{ uniqid() }}"
        x-ref="header-snippet">
        <div
            x-data="snippetEditor(headerStore,$el.id)"
            x-init="onInit"
            id="header-snippet"
            x-on:mousedown="printStore.editHeader && headerStore.snippetEditorXData === null ? headerStore.onMouseDown($event,$el.id,'snippet') : null"
            data-type="resizable"
            draggable="false"
            class="absolute w-[10cm] h-[1.7cm] border"
            :class="{
                    'border-primary-200': headerStore.isSnippetResizeClicked,
                    'bg-gray-100' : !headerStore.isResizeOrScaleActive && headerStore.selectedElementId === $el.id
                    }"
        >
            <div
                draggable="false"
                x-cloak x-show="printStore.editHeader" class="relative w-full h-full">
                <x-icon
                    x-cloak
                    x-show="headerStore.snippetEditorXData === null"
                    dragable="false"
                    x-on:mousedown="toggleEditor()"
                    name="pencil" class="absolute cursor-pointer left-0 top-0 h-4 w-4 rounded-full text-left"></x-icon>
                <template x-if="headerStore.snippetEditorXData?.elementObj.id === objId">
                    <x-flux::editor
                        x-editable="headerStore.snippetEditorXData?.elementObj.id === objId"
                        class="absolute top-0 left-0 w-full p-0"
                        x-modelable="content"
                        x-model="text"
                        :full-height="true"
                        :text-align="true"
                        :tooltip-dropdown="true"
                        :show-editor-padding="false"
                        :transparent="true" />
                </template>
                <div
                    x-cloak
                    x-show="headerStore.snippetEditorXData === null"
                    x-html="text">
                </div>
                <x-icon
                    x-cloak
                    x-show="headerStore.snippetEditorXData === null"
                    dragable="false"
                    x-on:mousedown.stop="headerStore.onMouseDownResize($event, $el.parentElement.parentElement.id,'snippet')"
                    name="arrows-pointing-out" class="absolute cursor-pointer right-0 bottom-0 h-4 w-4 rounded-full"></x-icon>
            </div>
            <div
                x-cloak
                x-show="!printStore.editHeader"
                x-html="text">
            </div>
        </div>
    </template>
</header>
