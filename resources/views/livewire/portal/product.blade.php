<div class="dark:text-white" x-data="{comment: @entangle('comment').defer}">
    <div>
        <div class="flex w-full flex-col-reverse justify-between md:flex-row">
            <h2 class="pt-5 text-base font-bold uppercase md:pt-0">
                {{ __('My products') }}
            </h2>
            @if(auth()->user()->can('service.{serialnumberid?}.get'))
                <x-button class="rounded-md" primary :label="__('Service request')" :href="route('portal.service', $serialNumber['id'])" />
            @endif
        </div>
        <h1 class="pt-5 text-5xl font-bold">
            {{ $serialNumber['product']['name'] ?? ''}}
        </h1>
    </div>
    <div class="w-full gap-10 space-y-3 pt-14 lg:flex lg:space-y-0">
        <div class="lg:w-1/3">
            <x-card>
                <div class="flex flex-none items-center">
                    <div class="w-full">
                        @if($productImage)
                            {!! $productImage !!}
                        @else
                            <div class="flex min-h-[12rem] w-full items-center justify-center rounded bg-gray-300 dark:bg-gray-700">
                                <svg class="h-12 w-12 text-gray-200" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" fill="currentColor" viewBox="0 0 640 512"><path d="M480 80C480 35.82 515.8 0 560 0C604.2 0 640 35.82 640 80C640 124.2 604.2 160 560 160C515.8 160 480 124.2 480 80zM0 456.1C0 445.6 2.964 435.3 8.551 426.4L225.3 81.01C231.9 70.42 243.5 64 256 64C268.5 64 280.1 70.42 286.8 81.01L412.7 281.7L460.9 202.7C464.1 196.1 472.2 192 480 192C487.8 192 495 196.1 499.1 202.7L631.1 419.1C636.9 428.6 640 439.7 640 450.9C640 484.6 612.6 512 578.9 512H55.91C25.03 512 .0006 486.1 .0006 456.1L0 456.1z"/></svg>
                            </div>
                        @endif
                    </div>
                </div>
            </x-card>
        </div>
        <div class="w-full space-y-3">
            <x-card>
                <h3 class="w-full text-center text-base font-bold">{{ __('Product information') }}</h3>
            </x-card>
            <x-card>
            <div class="w-full">
                <div class="p-5">
                    <x-additional-columns :table="true" wire="serialNumber" :model="\FluxErp\Models\SerialNumber::class" :id="$serialNumber['id']" />
                </div>
            </div>
            </x-card>
        </div>
    </div>
    <div class="gap-10 pt-8 lg:flex">
        @if(auth()->user()->can('files.get'))
        <div class="w-full space-y-3">
            <x-card>
                <h3 class="w-full px-8 text-base font-bold">{{ __('Documents') }}</h3>
            </x-card>
            <div class="w-full rounded-3xl">
                <div class="p-5">
                    <livewire:folder-tree :model-type="\FluxErp\Models\SerialNumber::class" :model-id="$serialNumber['id']" />
                </div>
            </div>
        </div>
        @endif
        <div class="w-full space-y-3">
            <x-card>
                <h3 class="w-full px-8 text-base font-bold">{{ __('My notes') }}</h3>
            </x-card>
            <x-card>
                <x-textarea x-model="comment" rows="5"></x-textarea>
            </x-card>
        </div>
    </div>
    <div class="flex justify-end pt-8">
        <x-button primary :label="__('Save')" wire:click="save" />
    </div>
</div>
