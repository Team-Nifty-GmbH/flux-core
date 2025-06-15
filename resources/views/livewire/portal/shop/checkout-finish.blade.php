<div class="flex flex-col justify-center gap-8">
    <div>
        <h1
            class="pt-5 pb-10 text-center text-5xl font-bold text-gray-900 dark:text-gray-50"
        >
            {{ __('Thank you for your order!') }}
        </h1>
    </div>
    <div class="flex justify-center">
        <x-button color="indigo" :href="route('portal.dashboard')">
            {{ __('Back to dashboard') }}
        </x-button>
    </div>
</div>
