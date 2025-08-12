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
</div>
