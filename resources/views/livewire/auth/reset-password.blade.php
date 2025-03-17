<div class="flex min-h-full flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <x-flux::logo fill="#0690FA" class="h-24" />
    </div>
    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
        <div class="bg-white px-4 py-8 shadow sm:rounded-lg sm:px-10">
            <div class="mt-6">
                <form class="flex flex-col gap-6" wire:submit="resetPassword()">
                    <x-password
                        wire:model="password"
                        :label="__('Set new password…')"
                    />
                    <x-password
                        wire:model="password_confirmation"
                        :label="__('Retype password…')"
                    />
                    <x-button
                        type="submit"
                        color="indigo"
                        class="w-full"
                        :text="__('Reset password')"
                    />
                </form>
            </div>
        </div>
    </div>
</div>
