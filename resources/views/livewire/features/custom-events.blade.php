<div
    x-data="{
        customEvents: $wire.entangle('customEvents', true)
    }"
>
    <div class="space-y-3 pb-3">
        <template x-for="customEvent in customEvents">
            <div class="flex justify-end">
                <x-button color="gray"
                          loading
                          class="w-full"
                          x-text="customEvent.name"
                          x-on:click="$wire.dispatchCustomEvent(customEvent.name, {{ is_array($additionalData) ? implode(', ', $additionalData) : $additionalData }})">
                </x-button>
            </div>
        </template>
    </div>
</div>
