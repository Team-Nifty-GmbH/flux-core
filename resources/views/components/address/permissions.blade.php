<div>
    @section('content')
    <div class="flex justify-between">
        <div>
            <h3
                class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-50"
            >
                {{ __('Customer portal') }}
            </h3>
        </div>
    </div>
    <div class="space-y-6 sm:space-y-5">
        <div
            class="dark:border-secondary-700 sm:grid sm:grid-cols-3 sm:items-start sm:gap-4 sm:pt-5"
        >
            <label
                for="{{ md5('address.can_login') }}"
                class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2 dark:text-gray-50"
            >
                {{ __('Active') }}
            </label>
            <div class="col-span-2">
                <x-toggle
                    md
                    x-bind:disabled="!$wire.edit"
                    wire:model="address.can_login"
                />
            </div>
        </div>
        <div
            class="dark:border-secondary-700 sm:grid sm:grid-cols-3 sm:items-start sm:gap-4 sm:pt-5"
        >
            <label
                for="{{ md5('address.email') }}"
                class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2 dark:text-gray-50"
            >
                {{ __('Login Email') }}
            </label>
            <div class="col-span-2">
                <x-input
                    x-bind:readonly="!$wire.edit"
                    wire:model="address.email"
                />
            </div>
        </div>
        <div
            class="dark:border-secondary-700 sm:grid sm:grid-cols-3 sm:items-start sm:gap-4 sm:pt-5"
        >
            <label
                for="{{ md5('password') }}"
                class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2 dark:text-gray-50"
            >
                {{ __('Password') }}
            </label>
            <div class="col-span-2">
                <x-password
                    x-bind:readonly="!$wire.edit"
                    wire:model="address.password"
                />
            </div>
        </div>
        <div
            class="dark:border-secondary-700 sm:grid sm:grid-cols-3 sm:items-start sm:gap-4 sm:pt-5"
        >
            <label
                for="{{ md5('permissions') }}"
                class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2 dark:text-gray-50"
            >
                {{ __('Permissions') }}
            </label>
            <div class="col-span-2 space-y-3">
                <x-button
                    color="indigo"
                    :text="__('Select all')"
                    x-bind:disabled="!$wire.edit"
                    x-on:click="$wire.permissions().then((permissions) => $wire.address.permissions = permissions.map(permission => permission.id))"
                />
                <template x-for="permission in $wire.permissions()">
                    <div class="flex">
                        <div>
                            <x-checkbox
                                x-bind:disabled="!$wire.edit"
                                x-model.number="$wire.address.permissions"
                                x-bind:value="permission.id"
                                x-bind:id="'permission-' + permission.id"
                            />
                        </div>
                        <span
                            class="pl-2"
                            x-text="permission.name"
                            x-bind:for="'permission-' + permission.id"
                        ></span>
                    </div>
                </template>
            </div>
        </div>
    </div>
    @show
</div>
