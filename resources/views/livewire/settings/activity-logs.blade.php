<x-modal id="activity-log-detail" :title="__('Activity Log')">
    <x-card class="flex flex-col gap-4">
        <x-input :label="__('Causer')" wire:model="activity.causer" disabled />
        <pre
            class="max-h-96 overflow-auto rounded-md bg-black p-1 font-mono text-white"
            x-text="JSON.stringify($wire.activity.properties, null, 2)"
        ></pre>
        <x-slot:footer>
            <x-button
                color="secondary"
                light
                x-on:click="$modalClose('activity-log-detail')"
                :text="__('Close')"
            />
        </x-slot>
    </x-card>
</x-modal>
