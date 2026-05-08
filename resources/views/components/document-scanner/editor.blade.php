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
            <div class="flex flex-1 items-center justify-center">
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
                    <div class="relative" x-ref="imageContainer">
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
                                <span x-text="brightness + '%'"></span>
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
                                <span x-text="contrast + '%'"></span>
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
