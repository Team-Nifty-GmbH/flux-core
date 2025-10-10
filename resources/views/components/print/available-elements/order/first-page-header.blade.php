<div x-cloak x-show="printStore.editFirstPageHeader">
    <div class="pb-4 text-lg text-gray-600">{{ __('Client') }}</div>
    <div class="flex flex-col gap-4">
        <div class="flex items-center justify-between">
            <div>{{ __('Name') }}</div>
            <x-toggle
                x-on:change="firstPageHeaderStore.toggleElement($refs,'first-page-header-client-name')"
                x-bind:value="firstPageHeaderStore.visibleElements.map(e => e.id).includes('first-page-header-client-name')"
            />
        </div>
        <div class="flex items-center justify-between">
            <div>{{ __('Inline Address') }}</div>
            <x-toggle
                x-on:change="firstPageHeaderStore.toggleElement($refs,'first-page-header-postal-address-one-line')"
                x-bind:value="firstPageHeaderStore.visibleElements.map(e => e.id).includes('first-page-header-postal-address-one-line')"
            />
        </div>
    </div>
    <div class="mb-4 mt-4 w-full border-t border-gray-300"></div>
    <div class="flex items-center justify-between">
        <div class="text-lg text-gray-600">{{ __('Subject') }}</div>
        <x-toggle
            x-on:change="firstPageHeaderStore.toggleElement($refs,'first-page-header-subject')"
            x-bind:value="firstPageHeaderStore.visibleElements.map(e => e.id).includes('first-page-header-subject')"
        />
    </div>
    <div class="mb-4 mt-4 w-full border-t border-gray-300"></div>
    <div class="pb-4 text-lg text-gray-600">{{ __('Order details') }}</div>
    <div class="flex flex-col gap-4">
        <div class="flex items-center justify-between">
            <div>{{ __('Address') }}</div>
            <x-toggle
                x-on:change="firstPageHeaderStore.toggleElement($refs,'first-page-header-address')"
                x-bind:value="firstPageHeaderStore.visibleElements.map(e => e.id).includes('first-page-header-address')"
            />
        </div>
        @if ($this->name === 'final-invoice')
            <div class="flex items-center justify-between">
                <div>{{ __('Order') }}</div>
                <x-toggle
                    x-on:change="firstPageHeaderStore.toggleElement($refs,'first-page-header-final-invoice')"
                    x-bind:value="firstPageHeaderStore.visibleElements.map(e => e.id).includes('first-page-header-final-invoice')"
                />
            </div>
        @elseif ($this->name === 'refund')
            <div class="flex items-center justify-between">
                <div>{{ __('Order') }}</div>
                <x-toggle
                    x-on:change="firstPageHeaderStore.toggleElement($refs,'first-page-header-refund')"
                    x-bind:value="firstPageHeaderStore.visibleElements.map(e => e.id).includes('first-page-header-refund')"
                />
            </div>
        @else
            <div class="flex items-center justify-between">
                <div>{{ __('Order') }}</div>
                <x-toggle
                    x-on:change="firstPageHeaderStore.toggleElement($refs,'first-page-header-right-block')"
                    x-bind:value="firstPageHeaderStore.visibleElements.map(e => e.id).includes('first-page-header-right-block')"
                />
            </div>
        @endif
    </div>
    <div class="mb-4 mt-4 w-full border-t border-gray-300"></div>
    <div class="pb-4 text-lg text-gray-600">{{ __('Additional Media') }}</div>
    <div
        :class="{'pb-4': firstPageHeaderStore.temporaryVisibleMedia.length > 0 || firstPageHeaderStore.visibleMedia.length > 0 }"
        class="flex flex-col gap-4"
    >
        <template
            x-for="(image, index) in firstPageHeaderStore.visibleMedia"
            :key="index"
        >
            <div class="flex items-center justify-between">
                <img
                    class="max-h-[1.7cm] select-none"
                    x-bind:src="image.src"
                />
                @canAction(\FluxErp\Actions\Media\DeleteMedia::class)
                    <x-button.circle
                        x-bind:disabled="firstPageHeaderStore.snippetEditorXData !== null"
                        x-on:click="firstPageHeaderStore.deleteMedia(image.id)"
                        icon="trash"
                    />
                @endcanAction
            </div>
        </template>
        {{-- not submited --}}
        <template
            x-for="(image, index) in firstPageHeaderStore.temporaryVisibleMedia"
            :key="index"
        >
            <div class="flex items-center justify-between">
                <img
                    class="max-h-[1.7cm] select-none"
                    x-bind:src="image.src"
                />
                <x-button.circle
                    x-bind:disabled="firstPageHeaderStore.snippetEditorXData !== null"
                    x-on:click="firstPageHeaderStore.deleteTemporaryMedia(image.id)"
                    icon="trash"
                />
            </div>
        </template>
        <label>
            <input
                x-ref="firstPageHeaderImageInput"
                type="file"
                accept="image/*"
                class="hidden"
                x-on:change="firstPageHeaderStore.addToTemporaryMedia($event, $refs)"
            />
            <x-button
                color="primary"
                text="{{__('Add Image')}}"
                x-on:click="$refs.firstPageHeaderImageInput.click()"
            />
        </label>
    </div>
    <div class="mb-4 mt-4 w-full border-t border-gray-300"></div>
    <div class="pb-4 text-lg text-gray-600">
        {{ __('Additional Snippets') }}
    </div>
    <div
        class="flex flex-col gap-4"
        x-bind:class="{
            'pb-4':
                firstPageHeaderStore.temporarySnippetBoxes.length > 0 ||
                firstPageHeaderStore.visibleSnippetBoxes.length > 0,
        }"
    >
        <template
            x-for="(snippet, index) in firstPageHeaderStore.snippetNames"
            :key="index"
        >
            <div class="flex items-center justify-between">
                <div
                    class="text-[12px] text-gray-400"
                    x-text="snippet.name"
                ></div>
                <x-button.circle
                    x-bind:disabled="firstPageHeaderStore.snippetEditorXData !== null"
                    x-on:click="firstPageHeaderStore.deleteSnippet(snippet.ref.id)"
                    icon="trash"
                />
            </div>
        </template>
    </div>
    <x-button
        color="primary"
        :text="__('Add Snippet')"
        x-on:click="firstPageHeaderStore.addToTemporarySnippet($refs)"
    />
</div>
