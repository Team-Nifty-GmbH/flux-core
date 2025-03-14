<div>
    <h2 class="text-base font-bold uppercase dark:text-gray-50">
        {{ $customerClient['name'] }} {{ __('Connect') }}
    </h2>
    <h1 class="pt-5 text-5xl font-bold dark:text-gray-50">
        {{ __('Service request') }}
    </h1>
    <h2 class="pb-8 pt-20 text-base font-bold uppercase dark:text-gray-50">
        01. {{ __('Information') }}
    </h2>
    <div class="md:flex md:space-x-12">
        <div class="flex-1">
            <div class="space-y-5 dark:text-gray-50">
                <div class="w-full grid-cols-2 md:grid md:gap-5">
                    <x-input
                        wire:model="contactData.firstname"
                        :placeholder="__('Firstname')"
                    />
                    <x-input
                        wire:model="contactData.lastname"
                        :placeholder="__('Lastname')"
                    />
                    <x-input
                        wire:model="contactData.email"
                        :placeholder="__('E-Mail')"
                    />
                    <x-input
                        wire:model="contactData.phone"
                        :placeholder="__('Phone')"
                    />
                </div>
                <x-input
                    wire:model="contactData.company"
                    :placeholder="__('Company')"
                />
                <x-input
                    wire:model="contactData.serial_number"
                    :placeholder="__('Serial number')"
                />
                <x-input
                    wire:model="ticket.title"
                    :placeholder="__('What is it about?')"
                />
                <x-textarea
                    wire:model="ticket.description"
                    :placeholder="__('Your subject')"
                />
                <h2 class="pb-8 pt-20 text-base font-bold uppercase">
                    02. {{ __('Attachments') }}
                </h2>
                <div class="text-portal-font-color font-bold">
                    {{ __('Photos and videos help us analyze the errors') }}
                </div>
                <div>
                    <x-flux::features.media.upload
                        wire:model.live="attachments"
                    />
                </div>
                <div class="text-portal-font-color pt-12 font-bold">
                    {{ __(':client_name is committed to protecting and respecting your privacy. We will only use your personal information to manage your account and to provide you with the products and services you have requested.', ['client_name' => $customerClient['name']]) }}
                </div>
                <x-button color="indigo" wire:click.prevent="save()">
                    {{ __('Send') }}
                </x-button>
                <x-errors />
            </div>
        </div>
        <div
            class="m-0 flex-none pt-16 md:max-w-[220px] md:pt-0 dark:text-gray-50"
        >
            <h2 class="text-base font-bold uppercase">
                {!! __('Urgent request?') !!}
            </h2>
            <div class="pt-8">
                {!! __('Your service request will be processed by our team immediately upon receipt. Do you have an acute problem, feel free to call us.') !!}
            </div>
            <div class="py-5 font-bold">
                <a href="tel:+{{ $customerClient['phone'] }}">
                    {{ $customerClient['phone'] }}
                </a>
            </div>
            <div class="grid grid-cols-2">
                @foreach ($customerClient['opening_hours'] ?? [] as $openingHour)
                    <div class="">
                        {{ $openingHour['day'] }}
                    </div>
                    <div class="">
                        {{ $openingHour['start'] }} -
                        {{ $openingHour['end'] }}
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
