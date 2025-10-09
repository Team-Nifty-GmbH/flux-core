<header
    class="relative h-[1.7cm] w-full bg-white text-center"
    x-bind:class="{ 'z-[100]': headerStore.snippetEditorXData !== null }"
    x-on:mouseup.window="headerStore.onMouseUp()"
    x-on:mousemove.window="
        headerStore.selectedElementId !== null && ! headerStore.isResizeOrScaleActive
            ? headerStore.onMouseMove($event)
            : null
    "
    x-bind:style="`height: ${headerStore.headerHeight};`"
>
    {{-- UI  header height related --}}
    <div
        x-on:mousedown="headerStore.onMouseDownHeader($event)"
        x-cloak
        x-show="printStore.editHeader && headerStore.snippetEditorXData === null "
        class="absolute bottom-0 left-1/2 z-[100] h-6 w-6 -translate-x-1/2 translate-y-1/2 cursor-pointer select-none rounded-full bg-flux-primary-400"
    >
        <div
            class="relative bottom-0 flex h-full w-full items-center justify-center"
        >
            <div
                x-text="headerStore.headerHeight"
                class="absolute -bottom-14 h-12 rounded bg-gray-100 p-2 text-lg shadow"
            ></div>
        </div>
    </div>
    <div
        x-cloak
        x-show="printStore.editHeader"
        class="absolute bottom-0 w-full border-b border-b-gray-300"
    ></div>
    {{-- UI position of a selected element --}}
    <div
        x-cloak
        x-show="!headerStore.isImgResizeClicked && headerStore.selectedElementId !== null"
        :style="{'transform': `translate(${headerStore.selectedElementPos.x -50}px,${headerStore.selectedElementPos.y}px)` }"
        class="absolute left-0 top-0 z-[100] rounded bg-gray-100 p-2 shadow"
    >
        <div
            x-text="`${roundToOneDecimal(headerStore.selectedElementPos.x / headerStore.pxPerCm)}cm`"
        ></div>
    </div>
    <div
        x-cloak
        x-show="!headerStore.isImgResizeClicked && headerStore.selectedElementId !== null"
        :style="{'transform': `translate(${headerStore.selectedElementPos.x}px,${headerStore.selectedElementPos.y - 40}px)` }"
        class="absolute left-0 top-0 z-[100] rounded bg-gray-100 p-2 shadow"
    >
        <div
            x-text="`${roundToOneDecimal(headerStore.selectedElementPos.y / headerStore.pyPerCm)}cm`"
        ></div>
    </div>
    {{-- UI position of a selected element --}}
    {{-- UI size of a snippet --}}
    <div
        x-cloak
        x-show="headerStore.isSnippetResizeClicked && headerStore.selectedElementId !== null"
        :style="{'transform': `translate(${headerStore.selectedElementPos.x -50}px,${headerStore.selectedElementPos.y}px)` }"
        class="absolute left-0 top-0 z-[100] rounded bg-gray-100 p-2 shadow"
    >
        <div
            x-text="`h:${roundToOneDecimal(headerStore.selectedElementSize.height / headerStore.pxPerCm)}cm`"
        ></div>
    </div>
    <div
        x-cloak
        x-show="headerStore.isSnippetResizeClicked && headerStore.selectedElementId !== null"
        :style="{'transform': `translate(${headerStore.selectedElementPos.x}px,${headerStore.selectedElementPos.y - 40}px)` }"
        class="absolute left-0 top-0 z-[100] rounded bg-gray-100 p-2 shadow"
    >
        <div
            x-text="`w: ${roundToOneDecimal(headerStore.selectedElementSize.width / headerStore.pyPerCm)}cm`"
        ></div>
    </div>
    {{-- UI size of a snippet --}}
    {{-- UI snippet box name --}}
    <template
        x-for="(box, index) in headerStore.snippetNames"
        :key="`${box.ref.id}-${index}`"
    >
        <div
            :style="{'transform': `translate(${box.ref.position.x}px,${box.ref.position.y - 15}px)` }"
            class="absolute left-0 top-0"
        >
            <div class="text-[12px] text-gray-400" x-text="box.name"></div>
        </div>
    </template>
    {{-- UI snippet box name --}}
    <div
        x-ref="header"
        x-on:mouseup.window="headerStore.onMouseUpHeader($event)"
        x-on:mousemove.window="headerStore.isHeaderClicked ? headerStore.onMouseMoveHeader($event) : null"
        class="header-content relative h-full w-full text-2xs leading-3"
    ></div>
    <div
        x-on:mousemove.window="
            headerStore.isImgResizeClicked
                ? headerStore.onMouseMoveScale($event)
                : headerStore.isSnippetResizeClicked
                  ? headerStore.onMouseMoveResize($event)
                  : null
        "
    ></div>
    <template
        id="{{ data_get($client, 'id', uniqid()) }}"
        x-ref="header-subject"
    >
        <div
            id="header-subject"
            draggable="false"
            data-type="container"
            class="absolute left-0 top-0 w-fit select-none"
            x-bind:class="{
                'bg-gray-300':
                    ! headerStore.isImgResizeClicked &&
                    headerStore.selectedElementId === 'header-subject',
            }"
            x-on:mousedown="printStore.editHeader ? headerStore.onMouseDown($event, 'header-subject') : null"
        >
            <x-flux::print.elements.header-subject :subject="$subject" />
        </div>
    </template>
    <template id="{{ data_get($client, 'id', uniqid()) }}" x-ref="header-logo">
        <div
            id="header-logo"
            draggable="false"
            data-type="resizable"
            x-on:mousedown="printStore.editHeader ? headerStore.onMouseDown($event, 'header-logo') : null"
            class="absolute left-0 top-0 h-[1.7cm] select-none"
            x-bind:class="{
                'bg-gray-300':
                    ! headerStore.isImgResizeClicked &&
                    headerStore.selectedElementId === 'header-logo',
            }"
        >
            <div
                draggable="false"
                x-cloak
                x-show="printStore.editHeader"
                class="relative w-full"
            >
                <x-icon
                    x-on:mousedown.stop="headerStore.onMouseDownScale($event, 'header-logo')"
                    name="arrows-pointing-out"
                    class="absolute right-0 top-0 h-4 w-4 cursor-pointer rounded-full"
                ></x-icon>
            </div>
            <x-flux::print.elements.header-logo :client="$client" />
        </div>
    </template>
    <template id="{{ uniqid() }}" x-ref="header-page-count">
        <div
            id="header-page-count"
            data-type="container"
            draggable="false"
            x-on:mousedown="
                printStore.editHeader
                    ? headerStore.onMouseDown($event, 'header-page-count')
                    : null
            "
            class="absolute left-0 top-0 w-fit select-none"
            x-bind:class="{
                'bg-gray-300':
                    ! headerStore.isImgResizeClicked &&
                    headerStore.selectedElementId === 'header-page-count',
            }"
        >
            <x-flux::print.elements.header-page-count :preview="false" />
        </div>
    </template>
    <template id="{{ uniqid() }}" x-ref="header-additional-img">
        <div
            id="header-img-placeholder"
            x-on:mousedown="
                printStore.editHeader
                    ? headerStore.onMouseDown($event, $el.id, 'temporary')
                    : null
            "
            data-type="resizable"
            draggable="false"
            class="absolute left-0 top-0 h-[1.7cm] select-none"
            x-bind:class="{
                'bg-gray-300':
                    ! headerStore.isImgResizeClicked &&
                    headerStore.selectedElementId === $el.id,
            }"
        >
            <div
                draggable="false"
                x-cloak
                x-show="printStore.editHeader"
                class="relative w-full"
            >
                <x-icon
                    x-on:mousedown.stop="headerStore.onMouseDownScale($event, $el.parentElement.parentElement.id,'temporary')"
                    name="arrows-pointing-out"
                    class="absolute right-0 top-0 h-4 w-4 cursor-pointer rounded-full"
                ></x-icon>
            </div>
            <img draggable="false" class="max-h-full w-full" src="" />
        </div>
    </template>
    <template id="{{ uniqid() }}" x-ref="header-media">
        <div
            id="header-media"
            x-on:mousedown="printStore.editHeader ? headerStore.onMouseDown($event, $el.id, 'media') : null"
            data-type="resizable"
            draggable="false"
            class="absolute left-0 top-0 h-[1.7cm] select-none"
            x-bind:class="{
                'bg-gray-300':
                    ! headerStore.isImgResizeClicked &&
                    headerStore.selectedElementId === $el.id,
            }"
        >
            <div
                draggable="false"
                x-cloak
                x-show="printStore.editHeader"
                class="relative w-full"
            >
                <x-icon
                    x-on:mousedown.stop="headerStore.onMouseDownScale($event, $el.parentElement.parentElement.id,'media')"
                    name="arrows-pointing-out"
                    class="absolute right-0 top-0 h-4 w-4 cursor-pointer rounded-full"
                ></x-icon>
            </div>
            <img draggable="false" class="max-h-full w-full" src="" />
        </div>
    </template>
    <template id="{{ uniqid() }}" x-ref="header-additional-snippet">
        <div
            x-data="temporarySnippetEditor(headerStore, $el.id)"
            x-init="onInit"
            x-on:mousedown="
                printStore.editHeader && headerStore.snippetEditorXData === null
                    ? headerStore.onMouseDown($event, $el.id, 'temporary-snippet')
                    : null
            "
            id="header-snippet-placeholder"
            data-type="resizable"
            draggable="false"
            class="absolute h-[1.7cm] w-[10cm] border"
            x-bind:class="{
                'border-primary-200': headerStore.isSnippetResizeClicked,
                'bg-gray-100':
                    ! headerStore.isResizeOrScaleActive &&
                    headerStore.selectedElementId === $el.id,
                'z-[-10]':
                    headerStore.snippetEditorXData !== null &&
                    headerStore.snippetEditorXData?.elementObj.id !== objId,
            }"
        >
            <div
                draggable="false"
                x-cloak
                x-show="printStore.editHeader"
                class="relative h-full w-full"
            >
                <x-icon
                    x-cloak
                    x-show="headerStore.snippetEditorXData === null"
                    dragable="false"
                    x-on:mousedown="toggleEditor()"
                    name="pencil"
                    class="absolute left-0 top-0 h-4 w-4 cursor-pointer rounded-full text-left"
                ></x-icon>
                <template
                    x-if="headerStore.snippetEditorXData?.elementObj.id === objId"
                >
                    <x-flux::editor
                        x-editable="headerStore.snippetEditorXData?.elementObj.id === objId"
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
                    x-show="headerStore.snippetEditorXData === null"
                    class="p-1 text-left"
                    x-html="text"
                ></div>
                <x-icon
                    x-cloak
                    x-show="headerStore.snippetEditorXData === null"
                    dragable="false"
                    x-on:mousedown.stop="headerStore.onMouseDownResize($event, $el.parentElement.parentElement.id,'temporary-snippet')"
                    name="arrows-pointing-out"
                    class="absolute bottom-0 right-0 h-4 w-4 cursor-pointer rounded-full"
                ></x-icon>
            </div>
        </div>
    </template>
    <template id="{{ uniqid() }}" x-ref="header-snippet">
        <div
            x-data="snippetEditor(headerStore, $el.id)"
            x-init="onInit"
            id="header-snippet"
            x-on:mousedown="
                printStore.editHeader && headerStore.snippetEditorXData === null
                    ? headerStore.onMouseDown($event, $el.id, 'snippet')
                    : null
            "
            data-type="resizable"
            draggable="false"
            class="absolute h-[1.7cm] w-[10cm] border"
            x-bind:class="{
                'border-primary-200': headerStore.isSnippetResizeClicked,
                'bg-gray-100':
                    ! headerStore.isResizeOrScaleActive &&
                    headerStore.selectedElementId === $el.id,
                'z-[-10]':
                    headerStore.snippetEditorXData !== null &&
                    headerStore.snippetEditorXData?.elementObj.id !== objId,
            }"
        >
            <div
                draggable="false"
                x-cloak
                x-show="printStore.editHeader"
                class="relative h-full w-full"
            >
                <x-icon
                    x-cloak
                    x-show="headerStore.snippetEditorXData === null"
                    dragable="false"
                    x-on:mousedown="toggleEditor()"
                    name="pencil"
                    class="absolute left-0 top-0 h-4 w-4 cursor-pointer rounded-full text-left"
                ></x-icon>
                <template
                    x-if="headerStore.snippetEditorXData?.elementObj.id === objId"
                >
                    <x-flux::editor
                        x-editable="headerStore.snippetEditorXData?.elementObj.id === objId"
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
                    class="text-[9.1px]"
                    x-show="headerStore.snippetEditorXData === null"
                    x-html="text"
                ></div>
                <x-icon
                    x-cloak
                    x-show="headerStore.snippetEditorXData === null"
                    dragable="false"
                    x-on:mousedown.stop="headerStore.onMouseDownResize($event, $el.parentElement.parentElement.id,'snippet')"
                    name="arrows-pointing-out"
                    class="absolute bottom-0 right-0 h-4 w-4 cursor-pointer rounded-full"
                ></x-icon>
            </div>
            <div
                x-cloak
                x-show="!printStore.editHeader"
                x-html="text"
            ></div>
        </div>
    </template>
</header>
