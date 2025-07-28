@props([
    'supportsDocumentPreview' => false,
])
@if ($supportsDocumentPreview)
    <x-modal
        id="preview-{{ strtolower($this->getId()) }}"
        size="6xl"
        :title="__('Preview')"
        x-on:close="$el.querySelector('iframe').src = 'data:text/html;charset=utf-8,%3Chtml%3E%3Cbody%3E%3C%2Fbody%3E%3C%2Fhtml%3E'"
    >
        <iframe
            id="preview-iframe"
            src="data:text/html;charset=utf-8,%3Chtml%3E%3Cbody%3E%3C%2Fbody%3E%3C%2Fhtml%3E"
            loading="lazy"
            class="min-h-screen w-full"
        ></iframe>
        <x-slot:footer>
            <x-button
                color="secondary"
                light
                flat
                :text="__('Cancel')"
                x-on:click="$modalClose('preview-{{ strtolower($this->getId()) }}')"
            />
            <x-button
                loading
                color="indigo"
                :text="__('Download')"
                wire:click="downloadPreview()"
            />
        </x-slot>
    </x-modal>
@endif

<x-modal
    id="create-documents-{{ strtolower($this->getId()) }}"
    :title="__('Create Documents')"
    size="3xl"
>
    <div class="w-full overflow-hidden overflow-x-auto">
        <div class="grid w-full grid-cols-4 gap-4 text-left text-sm">
            @canAction(\FluxErp\Actions\PrintJob\CreatePrintJob::class)
                @if (auth()->user()?->printers()->exists())
                    <div
                        class="overflow-hidden text-ellipsis whitespace-nowrap font-bold"
                    >
                        {{ __('Print') }}
                    </div>
                @endif
            @endcanAction

            <div
                class="overflow-hidden text-ellipsis whitespace-nowrap font-bold"
            >
                {{ __('Email') }}
            </div>
            <div
                class="overflow-hidden text-ellipsis whitespace-nowrap font-bold"
            >
                {{ __('Download') }}
            </div>
            <div
                class="overflow-hidden text-ellipsis whitespace-nowrap font-bold"
            >
                {{ __('Force Create') }}
            </div>
        </div>
        <div class="w-full divide-y divide-slate-300 dark:divide-slate-700">
            <template x-for="printLayout in $wire.printLayouts">
                <div class="grid w-full grid-cols-4 gap-4 py-2">
                    @canAction(\FluxErp\Actions\PrintJob\CreatePrintJob::class)
                        @if ($printers ?? false)
                            <div
                                class="overflow-hidden text-ellipsis whitespace-nowrap"
                            >
                                <x-checkbox
                                    class="truncate"
                                    wire:model="selectedPrintLayouts.print"
                                    x-bind:value="printLayout.layout"
                                    x-bind:checked="$wire.forcedPrintLayouts.print.includes(printLayout.layout)"
                                    x-bind:disabled="$wire.forcedPrintLayouts.print.includes(printLayout.layout)"
                                >
                                    <x-slot:label>
                                        <div
                                            x-text="printLayout.label"
                                        ></div>
                                    </x-slot>
                                </x-checkbox>
                            </div>
                        @endif
                    @endcanAction

                    <div
                        class="overflow-hidden text-ellipsis whitespace-nowrap"
                    >
                        <x-checkbox
                            class="truncate"
                            wire:model="selectedPrintLayouts.email"
                            x-bind:value="printLayout.layout"
                            x-bind:checked="$wire.forcedPrintLayouts.email.includes(printLayout.layout)"
                            x-bind:disabled="$wire.forcedPrintLayouts.email.includes(printLayout.layout)"
                        >
                            <x-slot:label>
                                <div x-text="printLayout.label"></div>
                            </x-slot>
                        </x-checkbox>
                    </div>
                    <div
                        class="overflow-hidden text-ellipsis whitespace-nowrap"
                    >
                        <x-checkbox
                            class="truncate"
                            wire:model="selectedPrintLayouts.download"
                            x-bind:value="printLayout.layout"
                            x-bind:checked="$wire.forcedPrintLayouts.download.includes(printLayout.layout)"
                            x-bind:disabled="$wire.forcedPrintLayouts.download.includes(printLayout.layout)"
                        >
                            <x-slot:label>
                                <div x-text="printLayout.label"></div>
                            </x-slot>
                        </x-checkbox>
                    </div>
                    <div
                        class="overflow-hidden text-ellipsis whitespace-nowrap"
                    >
                        <x-checkbox
                            class="truncate"
                            wire:model="selectedPrintLayouts.force"
                            x-bind:value="printLayout.layout"
                            x-bind:checked="$wire.forcedPrintLayouts.force.includes(printLayout.layout)"
                            x-bind:disabled="$wire.forcedPrintLayouts.force.includes(printLayout.layout)"
                        >
                            <x-slot:label>
                                <div x-text="printLayout.label"></div>
                            </x-slot>
                        </x-checkbox>
                    </div>
                </div>
            </template>
            @if ($printers ?? false)
                <div
                    class="flex flex-col gap-2 p-4"
                    x-collapse
                    x-cloak
                    x-show="$wire.selectedPrintLayouts.print.length > 0"
                >
                    <x-select.styled
                        :label="__('Printer')"
                        wire:model="printJobForm.printer_id"
                        x-on:select="$tallstackuiSelect('print-job-size').setOptions($event.detail.select.media_sizes)"
                        select="label:name|value:id|description:location"
                        :options="$printers"
                    />
                    <div
                        x-cloak
                        x-show="$wire.printJobForm.printer_id"
                        x-collapse
                    >
                        <x-number
                            min="1"
                            step="1"
                            wire:model="printJobForm.quantity"
                            :label="__('Copies')"
                            class="w-full"
                        />
                    </div>
                    <div
                        id="print-job-size"
                        x-cloak
                        x-show="$wire.printJobForm.printer_id"
                        x-collapse
                    >
                        <x-select.styled
                            :label="__('Size')"
                            wire:model="printJobForm.size"
                            :options="$mediaSizes"
                        />
                    </div>
                </div>
            @endif
        </div>
    </div>
    <x-slot:footer>
        <x-button
            color="secondary"
            light
            flat
            :text="__('Cancel')"
            x-on:click="$modalClose('create-documents-{{ strtolower($this->getId()) }}')"
        />
        <x-button
            color="indigo"
            :text="__('Continue')"
            loading="createDocuments"
            wire:click="createDocuments().then(() => { $modalClose('create-documents-{{ strtolower($this->getId()) }}'); });"
        />
    </x-slot>
</x-modal>
