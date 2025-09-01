<div x-cloak x-show="printStore.editHeader">
    <div  class="flex items-center justify-between">
        <div class=" text-lg text-gray-600">Subject</div>
        <x-toggle
            x-on:change="headerStore.toggleElement($refs,'header-subject')"
            x-bind:value="headerStore.visibleElements.map(e => e.id).includes('header-subject')"
        />
    </div>
    <div class="mb-4 mt-4 w-full border-t border-gray-300"></div>
    @if($this->client->logo_small_url)
        <div class="pb-4 text-lg text-gray-600">Logo</div>
        <div class="flex items-center justify-between">
            <img class="h-[1.7cm]" src="{{ $this->client->logo_small_url }}" />
            <x-toggle
                x-on:change="headerStore.toggleElement($refs,'header-logo')"
                x-bind:value="headerStore.visibleElements.map(e => e.id).includes('header-logo')"
            />
        </div>
    @endif
    <div class="mb-4 mt-4 w-full border-t border-gray-300"></div>
    <div class="pb-4 text-lg text-gray-600">Additional Photos</div>
    <div
        :class="{'pb-4': headerStore.temporaryVisibleMedia.length > 0 || headerStore.visibleMedia.length > 0 }"
        class="flex flex-col gap-4">
        <template x-for="(image, index) in headerStore.visibleMedia" :key="index">
            <div class="flex items-center justify-between">
                <img
                    class="max-h-[1.7cm] select-none"
                    x-bind:src=image.src
                />
                @canAction(\FluxErp\Actions\Media\DeleteMedia::class)
                <x-button.circle
                    x-on:click="headerStore.deleteMedia(image.id)"
                    icon="trash"
                />
                @endcanAction
            </div>
        </template>
        {{-- not submited --}}
        <template x-for="(image, index) in headerStore.temporaryVisibleMedia" :key="index">
            <div class="flex items-center justify-between">
                <img
                    class="max-h-[1.7cm] select-none"
                    x-bind:src=image.src
                />
                <x-button.circle
                    x-on:click="headerStore.deleteTemporaryMedia(image.id)"
                    icon="trash"
                />
            </div>
        </template>
    </div>
    <label>
        <input
            x-ref="headerImageInput"
            type="file"
            accept="image/*"
            class="hidden"
            x-on:change="
                headerStore.addToTemporaryMedia(
                    $event,
                    $refs
                )"
        />
        <x-button
            color="primary"
            text="Add Image"
            x-on:click="$refs.headerImageInput.click()"
        />
    </label>
</div>
