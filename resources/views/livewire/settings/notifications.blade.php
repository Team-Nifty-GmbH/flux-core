<div class="py-6">
    <x-modal
        id="edit-notification-settings-modal"
        wire="detailModal"
        x-on:close="$wire.closeModal()"
    >
        <x-slot name="title">
            {{ __('Notification Settings') }}
        </x-slot>
        <template x-for="(notificationChannel, name) in $wire.notification">
            <div
                class="space-y-2 pb-6"
                x-bind:hidden="notificationChannel.name === 'database'"
            >
                <div class="flex space-x-1.5">
                    <x-checkbox
                        x-model="notificationChannel.is_active"
                    ></x-checkbox>
                    <div x-text="notificationChannel.name"></div>
                </div>
                <template
                    x-for="(channelValue, index) in notificationChannel.channel_value"
                >
                    <div class="flex">
                        <div class="flex items-center pr-1.5 transition-all">
                            <x-button.circle
                                2xs
                                color="red"
                                text="-"
                                x-on:click.prevent="_.pull(notificationChannel.channel_value, channelValue)"
                            ></x-button.circle>
                        </div>
                        <div class="w-full">
                            <x-input
                                class="flex-grow"
                                x-model="notificationChannel.channel_value[index]"
                            ></x-input>
                        </div>
                    </div>
                </template>
                <x-button.circle
                    2xs
                    color="emerald"
                    text="+"
                    x-on:click="notificationChannel.channel_value.push(null)"
                />
            </div>
        </template>
        <x-slot:footer>
            <x-button
                color="secondary"
                light
                flat
                :text="__('Cancel')"
                x-on:click="$modalClose('edit-notification-settings-modal')"
            />
            <x-button color="indigo" :text="__('Save')" wire:click="save()" />
        </x-slot>
    </x-modal>
    <div class="px-4 sm:px-6 lg:px-8">
        <div class="sm:flex sm:items-center">
            <div class="sm:flex-auto">
                <h1 class="text-xl font-semibold dark:text-white">
                    {{ __('Notifications') }}
                </h1>
                <div class="mt-2 text-sm text-gray-300">
                    {{ __('Here you can manage notification settings...') }}
                </div>
            </div>
        </div>
        <x-flux::table>
            <x-slot name="header">
                <th class="col-span-2">{{ __('Notification') }}</th>
            </x-slot>
            <template
                x-for="(notification, key) in $wire.notificationSettings"
            >
                <x-flux::table.row>
                    <td>
                        <div x-text="$wire.translate(key)"></div>
                    </td>
                    <td>
                        <x-button
                            color="indigo"
                            :text="__('Edit')"
                            x-on:click="$wire.show(key)"
                        />
                    </td>
                </x-flux::table.row>
            </template>
        </x-flux::table>
    </div>
</div>
