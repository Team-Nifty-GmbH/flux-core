<div
    class="p-10"
    x-data="{
        notifications: $wire.entangle('notifications', true),
        notificationChannels: $wire.entangle('notificationChannels', true),
        notificationSettings: $wire.entangle('notificationSettings'),
        }"
>
    <form class="space-y-5">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div class="w-full md:flex md:space-x-6">
                <label for="avatar" style="cursor: pointer">
                    <x-avatar class="m-auto" size="w-24 h-24" src="{{ $avatar }}"/>
                </label>
                <input type="file" accept="image/*" id="avatar" class="hidden" wire:model.live="avatar"/>
                <div class="w-full space-y-5">
                    <x-input :label="__('Firstname')" wire:model="user.firstname"/>
                    <x-input :label="__('Lastname')" wire:model="user.lastname"/>
                </div>
            </div>
            <div class="space-y-5">
                <x-input :label="__('Email')" wire:model="user.email"/>
                <x-input :label="__('User code')" wire:model="user.user_code"/>
            </div>
        </div>
        <x-select
            wire:model="user.language_id"
            :label="__('Language')"
            :options="$languages"
            option-label="name"
            option-value="id"
        />
        <x-input type="password" :label="__('New password')" wire:model="user.password"/>
        <x-input type="password" :label="__('Repeat password')" wire:model="user.password_confirmation"/>
    </form>
    <x-table>
        <x-slot name="title">
            <h2 class="pt-6 dark:text-white">{{ __('Notifications') }}</h2>
        </x-slot>
        <x-slot name="header">
                <th>{{ __('Notification') }}</th>
                <template x-for="(notificationChannel, name) in notificationChannels">
                    <th>
                        <div x-text="name"/>
                    </th>
                </template>
        </x-slot>
        <template x-for="(notification,key) in notificationSettings">
            <tr>
                <td>
                    <div x-text="key"></div>
                </td>
                <template x-for="(channelSettings,channel) in notification">
                    <td>
                        <x-checkbox
                            x-bind:disabled="channelSettings.is_disabled"
                            x-model="notificationSettings[key][channel].is_active"
                        />
                    </td>
                </template>
            </tr>
        </template>
    </x-table>
    <div class="flex justify-end space-x-5 pt-5">
        <x-button :label="__('Cancel')" x-on:click="window.history.back()"/>
        <x-button primary :label="__('Save')" wire:click="save"/>
    </div>
</div>
