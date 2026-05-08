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
    <div class="mt-4">
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
                            x-on:click="removeDocument(doc.id)"
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
                    <span>{{ __('Uploading documents...') }}</span>
                    <span>
                        <span x-text="uploadProgress"></span>
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
