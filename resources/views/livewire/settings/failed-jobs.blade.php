<div>
    <x-modal id="show-failed-job" max-width="6xl">
        <x-card footer-classes="flex justify-end" class="flex flex-col gap-4">
            <pre class="max-h-96 p-1 font-mono bg-black text-white rounded-md overflow-auto">
                <template x-for="line in $wire.failedJob.exception">
                    <div class="flex gap-1.5">
                        <span x-html="line"></span>
                    </div>
                </template>
            </pre>
            <pre class="max-h-96 p-1 font-mono bg-black text-white rounded-md overflow-auto">
                <div class="flex gap-1.5">
                    <span x-text="JSON.stringify($wire.failedJob.payload, null, 4)"></span>
                </div>
            </pre>
            <x-slot:footer>
                <x-button color="secondary" light :text="__('Close')" x-on:click="$modalClose('show-failed-job')" />
            </x-slot:footer>
        </x-card>
    </x-modal>
</div>
