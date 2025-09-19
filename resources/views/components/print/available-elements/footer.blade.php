<div x-show="printStore.editFooter" x-cloak>
    <div class="pb-4 text-lg text-gray-600">Client</div>
    <div
        style="font-family: Montserrat"
        class="flex items-center justify-between">
        <x-flux::print.elements.client :client="$client" />
        <x-toggle
            x-on:change="footerStore.toggleElement($refs,'footer-client-{{$client->id}}')"
            x-bind:value="footerStore.visibleElements.map(e => e.id).includes('footer-client-{{$client->id}}')" />
    </div>
    <div class="mb-4 mt-4 w-full border-t border-gray-300"></div>
    <div class="pb-4 text-lg text-gray-600">Bank Connections</div>
    <div
        style="font-family: Montserrat"
        class="flex flex-col gap-4">
        @foreach ($this->client?->bankConnections ?? [] as $bankConnection)
            <div class="flex items-center justify-between">
                <x-flux::print.elements.bank-connection :bank-connection="$bankConnection"  />
                <x-toggle
                    x-on:change="footerStore.toggleElement($refs,'footer-bank-{{$bankConnection->id}}')"
                    x-bind:value="footerStore.visibleElements.map(e => e.id).includes('footer-bank-{{$bankConnection->id}}')" />
            </div>
        @endforeach
    </div>
    <div class="mb-4 mt-4 w-full border-t border-gray-300"></div>
    @if($this->client->logo_small_url)
        <div x-cloak x-show="printStore.editFooter">
            <div class="pb-4 text-lg text-gray-600">Logo</div>
            <div class="flex items-center justify-between">
                <div class="h-[1.7cm]">
                    <x-flux::print.elements.footer-logo :client="$client" />
                </div>
                <x-toggle
                    x-on:change="footerStore.toggleElement($refs,'footer-logo')"
                    x-bind:value="footerStore.visibleElements.map(e => e.id).includes('footer-logo')" />
            </div>
        </div>
        <div x-cloak x-show="printStore.editHeader">
            <div class="pb-4 text-lg text-gray-600">Logo</div>
            <div class="flex items-center justify-between">
                <img class="h-[1.7cm]" src="{{ $this->client->logo_small_url }}" />
                <x-toggle
                    x-on:change="headerStore.toggleElement($refs,'header-logo')"
                    x-bind:value="headerStore.visibleElements.map(e => e.id).includes('header-logo')"
                />
            </div>
        </div>
    @endif
    <div class="mb-4 mt-4 w-full border-t border-gray-300"></div>
    <div class="pb-4 text-lg text-gray-600">Additional Photos</div>
    <div
        :class="{'pb-4': footerStore.temporaryVisibleMedia.length > 0 || footerStore.visibleMedia.length > 0 }"
        class="flex flex-col gap-4">
        <template x-for="(image, index) in footerStore.visibleMedia" :key="index">
            <div class="flex items-center justify-between">
                <img
                    class="max-h-[1.7cm] select-none"
                    x-bind:src=image.src
                />
                @canAction(\FluxErp\Actions\Media\DeleteMedia::class)
                <x-button.circle
                    x-bind:disabled="footerStore.snippetEditorXData !== null"
                    x-on:click="footerStore.deleteMedia(image.id)"
                    icon="trash"
                />
                @endcanAction
            </div>
        </template>
        {{-- not submited --}}
    <template x-for="(image, index) in footerStore.temporaryVisibleMedia" :key="index">
        <div class="flex items-center justify-between">
            <img
                class="max-h-[1.7cm] select-none"
               x-bind:src=image.src
            />
            <x-button.circle
                x-bind:disabled="footerStore.snippetEditorXData !== null"
                x-on:click="footerStore.deleteTemporaryMedia(image.id)"
                icon="trash"
            />
        </div>
    </template>
    </div>
    <label>
        <input
            x-ref="footerImageInput"
            type="file"
            accept="image/*"
            class="hidden"
            x-on:change="
                footerStore.addToTemporaryMedia(
                    $event,
                    $refs
                )"
        />
        <x-button
            color="primary"
            text="Add Image"
            x-on:click="$refs.footerImageInput.click()"
        />
    </label>
    <div class="mb-4 mt-4 w-full border-t border-gray-300"></div>
    <div class="pb-4 text-lg text-gray-600">Additional Snippet</div>
    <div class="flex flex-col gap-4"
         :class="{'pb-4': footerStore.temporarySnippetBoxes.length > 0 || footerStore.visibleSnippetBoxes.length > 0 }"
    >
    <template x-for="(snippet, index) in footerStore.snippetNames" :key="index">
        <div class="flex items-center justify-between">
            <div class="text-gray-400 text-[12px]" x-text="snippet.name"></div>
            <x-button.circle
                x-bind:disabled="footerStore.snippetEditorXData !== null"
                x-on:click="footerStore.deleteSnippet(snippet.ref.id)"
                icon="trash"
            />
        </div>
    </template>
    </div>
    <x-button
        color="primary"
        text="Add Snippet"
        x-on:click="footerStore.addToTemporarySnippet($refs)"
    />
</div>

