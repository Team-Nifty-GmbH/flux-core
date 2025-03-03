@use('FluxErp\Providers\ViewServiceProvider')
<div>
    @vite([
        ViewServiceProvider::getRealPackageAssetPath('/resources/js/alpine.js', 'team-nifty-gmbh/flux-erp'),
        ViewServiceProvider::getRealPackageAssetPath('/resources/css/app.css', 'team-nifty-gmbh/flux-erp'),
    ])
    <div x-cloak x-show="id === null" x-data="signature($wire, $refs)" class="bg-gray-100 mt-12 lg:pt-10 lg:px-10 ">
        <div class="flex flex-col items-center lg:items-end px-10">
            <div class="flex flex-col lg:flex-row w-full items-center justify-between">
                <div class="flex-1 justify-center">
                    <div x-cloak class="pt-10 lg:pt-0 flex flex-col items-center justify-center" x-show="error || id">
                        <div class="w-10 h-10">
                            <template x-if="error">
                                <x-icon name="exclamation-triangle" />
                            </template>
                            <template x-if="id && !error">
                                <x-icon name="check-circle" />
                            </template>
                        </div>
                        <template x-if="error">
                            <p class="text-2xl">{{ __('Upload Failed') }}</p>
                        </template>
                        <template x-if="id && !error">
                            <p class="text-2xl">{{ __(':model saved', ['model' => __('Signature')]) }}</p>
                        </template>
                    </div>
                </div>
                <div class="flex flex-col gap-6 items-end pt-10 lg:pt-0 w-full">
                    <div class="w-full max-w-96">
                        <h1 class="text-xl mb-2">{{ __('Sign here') }}</h1>
                        <x-input errorless :placeholder="__('Name')" wire:model="signature.custom_properties.name" class="w-full mt-4" />
                    </div>
                    <canvas x-ref="canvas" height="200" width="500" class="rounded-md h-auto" />
                </div>
            </div>
            <div class="mt-4 h-8 mb-4 lg:mr-0 mr-10 flex justify-end w-full space-x-5">
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
