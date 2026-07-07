<div class="flex max-h-full flex-col gap-4 p-4">
    <div class="flex items-start justify-between gap-2">
        <h2
            class="truncate text-lg font-semibold text-gray-700 dark:text-gray-400"
        >
            {{ __('Unassigned Purchase Invoices') }}
        </h2>
        @canAction(\FluxErp\Actions\Media\UploadMedia::class)
            <x-button
                color="primary"
                icon="camera"
                sm
                :text="__('Scan')"
                x-on:click="$tsui.open.modal('unassigned-pi-scan-modal')"
            />
        @endcanAction
    </div>
    <hr />
    <div class="min-h-0 flex-1 overflow-y-auto">
        <x-list :items="$this->purchaseInvoices" searchable>
            <x-slot:empty>
                <div class="py-6 text-center text-sm text-gray-400">
                    {{ __('No unassigned purchase invoices.') }}
                </div>
            </x-slot:empty>
            @interact('item_menu', $item)
                <x-dropdown.items
                    :text="__('Edit')"
                    icon="pencil-square"
                    wire:click="goToPurchaseInvoice({{ $item['id'] }})"
                />
            @endinteract
        </x-list>
    </div>

    @canAction(\FluxErp\Actions\Media\UploadMedia::class)
        <x-modal
            id="unassigned-pi-scan-modal"
            size="xl"
            scrollable
            persistent
            :title="__('Scan Purchase Invoice')"
        >
            <div
                x-data="documentScanner($wire)"
                x-on:keydown.escape.window="
                    if (isEditing) {
                        closeEditor();
                        $event.stopPropagation();
                    }
                "
                class="space-y-4"
            >
                <x-flux::document-scanner.controls />
                <x-flux::document-scanner.editor />
            </div>

            <x-slot:footer>
                <x-button
                    color="secondary"
                    light
                    :text="__('Close')"
                    x-on:click="$tsui.close.modal('unassigned-pi-scan-modal')"
                />
            </x-slot:footer>
        </x-modal>
    @endcanAction
</div>
