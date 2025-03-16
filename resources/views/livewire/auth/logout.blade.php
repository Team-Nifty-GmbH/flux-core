<div
    class="flex min-h-full flex-col justify-center py-12 sm:px-6 lg:px-8"
    x-data="{
        redirect: 5,
        init() {
            setInterval(() => {
                this.redirect--;
                if (this.redirect === 0) {
                    $wire.redirectToLogin();
                }
            }, 1000);
        }
    }"
>
    @section('content')
        @section('content.logo')
            <div class="sm:mx-auto sm:w-full sm:max-w-md">
                <x-flux::logo fill="#0690FA" class="h-24"/>
            </div>
        @show
        @section('content.form')
            <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
                <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10">
                    <div class="flex flex-col gap-4">
                        <p class="font-semibold">{{ __('Logged out successfully.') }}</p>
                        <p>{{ __('You will be redirected to the login page in') }} <span x-text="redirect"></span></p>
                        <x-button color="indigo" :text="__('Login')" class="w-full" wire:click="redirectToLogin()"></x-button>
                    </div>
                </div>
            </div>
        @show
    @show
</div>
