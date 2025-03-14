<div class="flex flex-col gap-8 justify-center">
    <div>
        <h1 class="pt-5 pb-10 text-5xl font-bold text-center text-gray-900 dark:text-gray-50">
            {{ __('Thank you for your order!') }}
        </h1>
    </div>
    <div class="flex justify-center">
        <x-button color="indigo" :href="route('portal.dashboard')">{{ __('Back to dashboard') }}</x-button>
    </div>
</div>
