@use("FluxErp\Providers\ViewServiceProvider")
<div>
    @vite([
        ViewServiceProvider::getRealPackageAssetPath(
            "/resources/js/alpine.js",
            "team-nifty-gmbh/flux-erp",
        ),
        ViewServiceProvider::getRealPackageAssetPath(
            "/resources/css/app.css",
            "team-nifty-gmbh/flux-erp",
        ),
    ])
    <div
        x-cloak
        x-show="id === null"
        x-data="signature($wire, $refs)"
        class="mt-12 bg-gray-100 lg:px-10 lg:pt-10"
    >
        <div class="flex flex-col items-center px-10 lg:items-end">
            <div
                class="flex w-full flex-col items-center justify-between lg:flex-row"
            >
                <div class="flex-1 justify-center">
                    <div
                        x-cloak
                        class="flex flex-col items-center justify-center pt-10 lg:pt-0"
                        x-show="error || id"
                    >
                        <div class="h-10 w-10">
                            <template x-if="error">
                                <x-icon name="exclamation-triangle" />
                            </template>
                            <template x-if="id && !error">
                                <x-icon name="check-circle" />
                            </template>
                        </div>
                        <template x-if="error">
                            <p class="text-2xl">{{ __("Upload Failed") }}</p>
                        </template>
                        <template x-if="id && !error">
                            <p class="text-2xl">
                                {{ __(":model saved", ["model" => __("Signature")]) }}
                            </p>
                        </template>
                    </div>
                </div>
                <div class="flex w-full flex-col items-end gap-6 pt-10 lg:pt-0">
                    <div class="w-full max-w-96">
                        <h1 class="mb-2 text-xl">{{ __("Sign here") }}</h1>
                        <x-input
                            errorless
                            :placeholder="__('Name')"
                            wire:model="signature.custom_properties.name"
                            class="mt-4 w-full"
                        />
                    </div>
                    <canvas
                        x-ref="canvas"
                        height="200"
                        width="500"
                        class="h-auto rounded-md"
                    />
                </div>
            </div>
            <div
                class="mb-4 mr-10 mt-4 flex h-8 w-full justify-end space-x-5 lg:mr-0"
            >
                <x-button
                    x-cloak
                    x-show="!isEmpty"
                    x-on:click="clear"
                    color="red"
                    :text="__('Clear')"
                />
                <x-button
                    x-cloak
                    x-show="!isEmpty"
                    x-on:click="save"
                    color="indigo"
                    :text="__('Save')"
                />
            </div>
        </div>
    </div>
    <livewire:features.comments.comments
        lazy
        :model-type="\FluxErp\Models\Order::class"
        :model-id="$modelInstance->id"
    />
</div>
