<div>
    @section('content')
        <div class="flex justify-between">
            <div>
                <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-50">
                    {{ __('Customer portal') }}
                </h3>
            </div>
        </div>
        <div class="space-y-6 sm:space-y-5">
            <div class="dark:border-secondary-700 sm:grid sm:grid-cols-3 sm:items-start sm:gap-4 sm:pt-5">
                <label for="{{ md5('address.can_login') }}" class="block text-sm font-medium text-gray-700 dark:text-gray-50 sm:mt-px sm:pt-2">
                    {{ __('Active') }}
                </label>
                <div class="col-span-2">
                    <x-toggle md x-bind:disabled="!$wire.edit" wire:model="address.can_login"/>
                </div>
            </div>
            <div
                class="dark:border-secondary-700 sm:grid sm:grid-cols-3 sm:items-start sm:gap-4 sm:pt-5">
                <label for="{{ md5('address.login_name') }}"
                       class="block text-sm font-medium text-gray-700 dark:text-gray-50 sm:mt-px sm:pt-2">
                    {{ __('Login name') }}
                </label>
                <div class="col-span-2">
                    <x-input x-bind:readonly="!$wire.edit" wire:model="address.login_name"/>
                </div>
            </div>
            <div
                class="dark:border-secondary-700 sm:grid sm:grid-cols-3 sm:items-start sm:gap-4 sm:pt-5">
                <label for="{{ md5('password') }}" class="block text-sm font-medium text-gray-700 dark:text-gray-50 sm:mt-px sm:pt-2">
                    {{ __('Password') }}
                </label>
                <div class="col-span-2">
                    <x-inputs.password x-bind:readonly="!$wire.edit" wire:model="address.login_password"/>
                </div>
            </div>
            <div
                class="dark:border-secondary-700 sm:grid sm:grid-cols-3 sm:items-start sm:gap-4 sm:pt-5">
                <label for="{{ md5('permissions') }}" class="block text-sm font-medium text-gray-700 dark:text-gray-50 sm:mt-px sm:pt-2">
                    {{ __('Permissions') }}
                </label>
                <div class="col-span-2 space-y-3">
                    <x-button
                        primary
                        :label="__('Select all')"
                        x-bind:disabled="!$wire.edit"
                        x-on:click="$wire.address.permissions = $wire.permissions.map(permission => permission.id)"
                    />
                    <template x-for="permission in $wire.permissions()">
                        <div class="flex">
                            <div>
                                <x-checkbox
                                    x-bind:disabled="!$wire.edit"
                                    wire:model.number="address.permissions"
                                    x-bind:value="permission.id"
                                    x-on:change="save = true"
                                    x-bind:id="'permission-' + permission.id"
                                />
                            </div>
                            <x-label class="pl-2" x-text="permission.name" x-bind:for="'permission-' + permission.id"/>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    @show
</div>
