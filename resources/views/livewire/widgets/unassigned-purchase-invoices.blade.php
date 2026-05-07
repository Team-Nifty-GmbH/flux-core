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
            >
                <input
                    x-ref="cameraInput"
                    type="file"
                    accept="image/*"
                    capture="environment"
                    class="hidden"
                    x-on:change="handleFileSelect($event)"
                />
                <input
                    x-ref="fileInput"
                    type="file"
                    accept="image/*"
                    class="hidden"
                    x-on:change="handleFileSelect($event)"
                />

                <div class="space-y-4">
                    <div class="flex justify-center gap-4">
                        <x-button
                            :text="__('Camera')"
                            icon="camera"
                            color="primary"
                            x-on:click="captureFromCamera()"
                        />
                        <x-button
                            :text="__('Gallery')"
                            icon="photo"
                            color="secondary"
                            x-on:click="pickFromGallery()"
                        />
                    </div>

                    <template x-if="hasDocuments">
                        <div>
                            <h4
                                class="mb-2 text-sm font-medium text-gray-700 dark:text-gray-300"
                            >
                                {{ __('Scanned Documents') }} (
                                <span x-text="scannedDocuments.length"></span>
                                )
                            </h4>
                            <div
                                class="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4"
                            >
                                <template
                                    x-for="doc in scannedDocuments"
                                    x-bind:key="doc.id"
                                >
                                    <div
                                        class="overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700"
                                    >
                                        <img
                                            x-bind:src="doc.thumbnail"
                                            alt=""
                                            class="aspect-[3/4] w-full object-cover"
                                        />
                                        <div
                                            class="flex items-center justify-center border-t border-gray-200 bg-gray-50 p-1.5 dark:border-gray-700 dark:bg-gray-800"
                                        >
                                            <x-button
                                                icon="trash"
                                                color="red"
                                                flat
                                                sm
                                                x-on:click="
                                                    removeDocument(doc.id)
                                                "
                                            />
                                        </div>
                                    </div>
                                </template>
                            </div>

                            <div class="mt-4">
                                <div x-cloak x-show="isUploading" class="mb-3">
                                    <div
                                        class="flex items-center justify-between text-sm text-gray-500 dark:text-gray-400"
                                    >
                                        <span>
                                            {{ __('Uploading documents...') }}
                                        </span>
                                        <span>
                                            <span
                                                x-text="uploadProgress"
                                            ></span>
                                            /
                                            <span x-text="uploadTotal"></span>
                                        </span>
                                    </div>
                                    <div
                                        class="mt-1 h-[2px] w-full bg-gray-200 dark:bg-gray-700"
                                    >
                                        <div
                                            class="h-[2px] bg-blue-500"
                                            style="transition: width 1s"
                                            x-bind:style="
                                                'width: ' +
                                                (uploadTotal > 0
                                                    ? Math.round(
                                                          (uploadProgress /
                                                              uploadTotal) *
                                                              100,
                                                      )
                                                    : 0) +
                                                '%'
                                            "
                                        ></div>
                                    </div>
                                </div>
                                <div class="flex justify-end">
                                    <x-button
                                        :text="__('Upload Documents')"
                                        color="primary"
                                        icon="cloud-arrow-up"
                                        x-bind:disabled="isUploading"
                                        x-on:click="uploadAll()"
                                    />
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <template x-teleport="body">
                    <div
                        x-cloak
                        x-show="isEditing"
                        class="fixed inset-0 z-[99] flex flex-col overflow-y-auto bg-white dark:bg-gray-900"
                    >
                        <div
                            class="flex shrink-0 items-center justify-between border-b border-gray-200 p-3 dark:border-gray-700"
                        >
                            <h3
                                class="text-sm font-medium text-gray-700 dark:text-gray-300"
                            >
                                {{ __('Scan') }}
                            </h3>
                            <x-button
                                icon="x-mark"
                                color="secondary"
                                flat
                                sm
                                x-on:click="closeEditor()"
                            />
                        </div>

                        <template x-if="isProcessing">
                            <div
                                class="flex flex-1 items-center justify-center"
                            >
                                <div
                                    class="text-center text-gray-700 dark:text-gray-300"
                                >
                                    <x-icon
                                        name="arrow-path"
                                        class="mx-auto mb-4 h-8 w-8 animate-spin"
                                    />
                                    <p>{{ __('Detecting document...') }}</p>
                                </div>
                            </div>
                        </template>

                        <template x-if="!isProcessing && cornerPoints">
                            <div class="flex min-h-0 flex-1 flex-col">
                                <div
                                    x-cloak
                                    x-show="detectionFailed"
                                    class="shrink-0 bg-amber-50 p-3 text-center text-sm text-amber-700 dark:bg-amber-900/30 dark:text-amber-400"
                                >
                                    {{ __('Document edges could not be detected. Adjust the corners manually.') }}
                                </div>

                                <div
                                    class="flex min-h-0 flex-1 items-center justify-center overflow-hidden bg-gray-900"
                                >
                                    <div
                                        class="relative"
                                        x-ref="imageContainer"
                                    >
                                        <img
                                            x-ref="originalImage"
                                            x-bind:src="originalImage"
                                            class="max-h-full max-w-full"
                                            x-on:load="drawCorners()"
                                        />
                                        <canvas
                                            x-ref="cornerCanvas"
                                            class="absolute top-0 left-0"
                                            style="touch-action: none"
                                            x-on:mousedown="startDrag($event)"
                                            x-on:mousemove="moveDrag($event)"
                                            x-on:mouseup="stopDrag()"
                                            x-on:mouseleave="stopDrag()"
                                            x-on:touchstart="startDrag($event)"
                                            x-on:touchmove="moveDrag($event)"
                                            x-on:touchend="stopDrag()"
                                        ></canvas>
                                    </div>
                                </div>

                                <div
                                    class="flex shrink-0 flex-col gap-3 bg-white p-4 dark:bg-gray-800"
                                >
                                    <p
                                        class="text-center text-xs text-gray-500 dark:text-gray-400"
                                    >
                                        {{ __('Drag the corners to adjust the document area.') }}
                                    </p>

                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label
                                                class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300"
                                            >
                                                {{ __('Brightness') }}:
                                                <span
                                                    x-text="brightness + '%'"
                                                ></span>
                                            </label>
                                            <input
                                                type="range"
                                                min="50"
                                                max="200"
                                                x-model="brightness"
                                                x-on:input="updateFilters()"
                                                class="w-full"
                                            />
                                        </div>
                                        <div>
                                            <label
                                                class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300"
                                            >
                                                {{ __('Contrast') }}:
                                                <span
                                                    x-text="contrast + '%'"
                                                ></span>
                                            </label>
                                            <input
                                                type="range"
                                                min="50"
                                                max="200"
                                                x-model="contrast"
                                                x-on:input="updateFilters()"
                                                class="w-full"
                                            />
                                        </div>
                                    </div>

                                    <div class="flex justify-end gap-2">
                                        <x-button
                                            :text="__('Cancel')"
                                            color="secondary"
                                            flat
                                            x-on:click="closeEditor()"
                                        />
                                        <x-button
                                            :text="__('Done')"
                                            color="primary"
                                            icon="check"
                                            x-on:click="applyAndAddToQueue()"
                                        />
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </template>
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
