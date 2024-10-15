<x-flux::layouts.app>
    <div class="flex min-h-full flex-col justify-center py-12 sm:px-6 lg:px-8" x-data>
        <div class="sm:mx-auto sm:w-full sm:max-w-md">
            <x-flux::logo fill="#0690FA" class="h-24"/>
        </div>
        <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
            <x-card>
                <div class="py-6">
                    <div>
                        {{ __('The login link is invalid or has expired.') }}
                    </div>
                    <div>
                        {{ __('Please request a new one.') }}
                    </div>
                </div>
                <x-button primary class="w-full" :label="__('Back to Login')" :href="route('login')"></x-button>
            </x-card>
        </div>
    </div>
</x-flux::layouts.app>
