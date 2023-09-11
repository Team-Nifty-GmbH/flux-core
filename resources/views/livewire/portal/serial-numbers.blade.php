<div class="dark:text-white">
    <h2 class="text-base font-bold uppercase">
        {{ __('Welcome') }}
    </h2>
    <h1 class="pt-5 text-5xl font-bold">
        {{ __('My products') }}
    </h1>
    <div class="pt-12">
        <x-input wire:model.live.debounce.500ms="search" :placeholder="__('Product name, description…')" />
    </div>
    <div class="grid grid-cols-1 gap-10 pt-14 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
        @foreach($serialNumbers as $serialNumber)
                <div>
                <a href="{{ auth()->user()->can('service.{serialnumberid?}.get') ? route('portal.product', $serialNumber->id) : '#' }}">
                    <div class="bg-portal-light w-full rounded-md">
                        @if($image = $serialNumber->product?->getFirstMedia('images'))
                            {{ $image }}
                        @else
                            <div class="flex min-h-[12rem] w-full items-center justify-center rounded bg-gray-300 dark:bg-gray-700">
                                <svg class="h-12 w-12 text-gray-200" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" fill="currentColor" viewBox="0 0 640 512"><path d="M480 80C480 35.82 515.8 0 560 0C604.2 0 640 35.82 640 80C640 124.2 604.2 160 560 160C515.8 160 480 124.2 480 80zM0 456.1C0 445.6 2.964 435.3 8.551 426.4L225.3 81.01C231.9 70.42 243.5 64 256 64C268.5 64 280.1 70.42 286.8 81.01L412.7 281.7L460.9 202.7C464.1 196.1 472.2 192 480 192C487.8 192 495 196.1 499.1 202.7L631.1 419.1C636.9 428.6 640 439.7 640 450.9C640 484.6 612.6 512 578.9 512H55.91C25.03 512 .0006 486.1 .0006 456.1L0 456.1z"/></svg>
                            </div>
                        @endif
                    </div>
                    <h2 class="pt-6 pb-3 text-center text-base font-bold uppercase">{{ $serialNumber->product?->name }}</h2>
                    <div class="leading-6">
                        @foreach($serialNumber->additionalColumns()->get()->where('is_customer_editable', true) as $additionalColumn)
                            <p class="text-center">
                                {{ __($additionalColumn->label) }}: {{ $serialNumber->{$additionalColumn->name} }}
                            </p>
                        @endforeach
                    </div>
                    <p class="text-center">{{ __('Serial number:') }} {{ $serialNumber->serial_number }}</p>
                </a>
                    <div class="flex flex-col gap-y-3 pt-3">
                        @if(auth()->user()->can('service.{serialnumberid?}.get'))
                            <div class="m-auto flex w-44 justify-center">
                                <x-button rounded primary :href="route('portal.service', $serialNumber->id)" :label="__('Service request')" />
                            </div>
                        @endif
                        @if(auth()->user()->can('product.{id}.get'))
                            <div class="m-auto flex w-44 justify-center">
                                <x-button rounded primary :href="route('portal.product', $serialNumber->id)" :label="__('My documents')" />
                            </div>
                        @endif
                    </div>
                </div>
        @endforeach
    </div>
</div>
