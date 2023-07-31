<div class="py-6"
     x-data="{
        notifications: $wire.entangle('notifications'),
        notificationChannels: $wire.entangle('notificationChannels'),
        notificationSettings: $wire.entangle('notificationSettings').defer,
        notification: $wire.entangle('notification').defer,
        }"
>
    <x-modal.card wire:model="detailModal">
        <x-slot name="title">
            {{ __('Notification Settings') }}
        </x-slot>
        <template x-for="(notificationChannel, name) in notification">
            <div class="space-y-2 pb-6" x-bind:hidden="notificationChannel.name === 'database'">
                <div class="flex space-x-1.5">
                    <x-checkbox x-model="notificationChannel.is_active"></x-checkbox>
                    <div x-text="notificationChannel.name"></div>
                </div>
                <template x-for="(channelValue, index) in notificationChannel.channel_value">
                        <div class="flex">
                            <div class="flex items-center pr-1.5 transition-all">
                                <x-button.circle 2xs negative label="-" x-on:click.prevent="_.pull(notificationChannel.channel_value, channelValue)"></x-button.circle>
                            </div>
                            <div class="w-full">
                                <x-input class="flex-grow" x-model="notificationChannel.channel_value[index]">
                                </x-input>
                            </div>
                        </div>
                </template>
                <x-button.circle 2xs positive label="+" x-on:click="notificationChannel.channel_value.push(null)"/>
            </div>
        </template>
        <x-slot name="footer">
            <div class="w-full">
                <div
                    class="flex justify-end gap-x-4">
                    <div class="flex">
                        <x-button flat :label="__('Cancel')" x-on:click="close"/>
                        <x-button primary :label="__('Save')" wire:click="save()"/>
                    </div>
                </div>
            </div>
        </x-slot>
    </x-modal.card>
    <div class="px-4 sm:px-6 lg:px-8">
        <div class="sm:flex sm:items-center">
            <div class="sm:flex-auto">
                <h1 class="text-xl font-semibold">{{ __('Notifications') }}</h1>
                <div class="mt-2 text-sm text-gray-300">{{__('Here you can manage notification settings..')}}</div>
            </div>
        </div>
        <x-table>
            <x-slot name="header">
                <th class="col-span-2">{{ __('Notification') }}</th>
            </x-slot>
            <template x-for="(notification,key) in notificationSettings">
                <x-table.row>
                    <td>
                        <div x-text="key"></div>
                    </td>
                    <td>
                        <x-button primary :label="__('Edit')" x-on:click="$wire.show(key)" />
                    </td>
                </x-table.row>
            </template>
        </x-table>
    </div>
</div>
