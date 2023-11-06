<div class="flex min-h-full flex-col justify-center py-12 sm:px-6 lg:px-8" x-data>
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <x-logo fill="#000000" class="h-24"/>
    </div>
    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
        <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10">
            <div class="mt-6">
                @section('password-reset-dialog')
                    <x-modal name="password-reset">
                        <x-card :title="__('Reset password')">
                            <x-input wire:model="email" :label="__('Email')" name="reset-email" type="email" required/>
                            <x-slot:footer>
                                <x-button wire:click="resetPassword()" primary class="w-full" :label="__('Reset password')" x-on:click="close()"></x-button>
                            </x-slot:footer>
                        </x-card>
                    </x-modal>
                @show
                @section('login-form')
                    <form class="flex flex-col gap-6" wire:submit="login()">
                        <x-input id="email" wire:model="email" :label="__('Email')" name="email" type="email" required autofocus/>
                        <x-inputs.password  wire:model="password" :label="__('Password')" id="password" name="password" required/>
                        @if($showPasswordReset)
                            <div class="flex items-center justify-between">
                                <div class="text-sm">
                                    <a x-on:click="$openModal('password-reset')" class="font-medium text-indigo-600 hover:text-indigo-500 cursor-pointer"> {{ __('Reset password') }}</a>
                                </div>
                            </div>
                        @endif
                        <x-button spinner primary class="w-full" :label="__('Login')" type="submit" dusk="login-button"></x-button>
                    </form>
                @show
            </div>
        </div>
    </div>
</div>
