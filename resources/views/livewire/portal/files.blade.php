<div class="dark:text-white">
    <h2 class="text-base font-bold uppercase">
        {{ $customerClient['name'] }} {{ __('Connect') }}
    </h2>
    <h1 class="pt-5 text-5xl font-bold">
        {{ __('My documents') }}
    </h1>
    <div class="pt-20 lg:flex lg:space-x-16">
        <div class="flex-1 space-y-8">
            @foreach($serialNumbers as $serialNumber)
                <h2 class="pb-2 text-base font-bold uppercase">{{ str_pad($loop->index + 1, 2, '0', 0) }}
                    . {{ $serialNumber['product']['name'] ?? ''}} / {{ $serialNumber['serial_number'] }}</h2>
                <livewire:folder-tree :model-type="\FluxErp\Models\SerialNumber::class" :model-id="$serialNumber['id']" />
            @endforeach
        </div>
        <div class="m-0 flex-none md:max-w-[220px] md:pt-0">
            <div class="pt-16">
                <h2 class="text-base font-bold uppercase">{{ __('Urgent request?') }}</h2>
                <div class="pt-8">
                    {{ __('Your service request will be processed by our team immediately upon receipt. Do you have an
                    acute problem, feel free to call us') }}
                </div>
                <div class="py-5 font-bold">
                    <a href="tel:+{{ $customerClient['phone'] }}">
                        {{ $customerClient['phone'] }}
                    </a>
                </div>
                @foreach(($customerClient['opening_hours'] ?? []) as $openingHour)
                    <div class="">
                        {{ $openingHour['day'] }}
                    </div>
                    <div class="">
                        {{ $openingHour['start'] }} - {{ $openingHour['end'] }}
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
