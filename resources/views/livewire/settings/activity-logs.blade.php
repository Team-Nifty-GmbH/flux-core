<x-modal id="activity-log-detail">
    <x-card class="flex flex-col gap-4">
        <x-input :label="__('Causer')" wire:model="activity.causer" disabled />
        <pre class="max-h-96 p-1 font-mono bg-black text-white rounded-md overflow-auto" x-text="JSON.stringify($wire.activity.properties, null, 2)">
        </pre>
        <x-slot:footer>
            <x-button color="secondary" light x-on:click="$modalClose('activity-log-detail')" :text="__('Close')" />
        </x-slot:footer>
    </x-card>
</x-modal>
