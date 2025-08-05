@use(\Illuminate\Support\Str)
<div
    class="p-10"
    x-data="{
        notifications: $wire.entangle('notifications', true),
        notificationChannels: $wire.entangle('notificationChannels', true),
        notificationSettings: $wire.entangle('notificationSettings'),
    }"
>
    @section('profile')
    @section('profile.form')
    <form class="space-y-5">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div class="w-full md:flex md:space-x-6">
                <label for="avatar" style="cursor: pointer">
                    <x-avatar
                        class="m-auto"
                        size="w-24 h-24"
                        :image="$avatar"
                    />
                </label>
                <input
                    type="file"
                    accept="image/*"
                    id="avatar"
                    class="hidden"
                    wire:model.live="avatar"
                />
                <div class="w-full space-y-5">
                    <x-input
                        :label="__('Firstname')"
                        wire:model="user.firstname"
                    />
                    <x-input
                        :label="__('Lastname')"
                        wire:model="user.lastname"
                    />
                </div>
            </div>
            <div class="space-y-5">
                <x-input :label="__('Email')" wire:model="user.email" />
                <x-input
                    :label="__('User code')"
                    wire:model="user.user_code"
                />
            </div>
        </div>
        <x-select.styled
            wire:model="user.language_id"
            :label="__('Language')"
            select="label:name|value:id"
            :options="$languages"
        />
        <x-password :label="__('New password')" wire:model="user.password" />
        <x-password
            :label="__('Repeat password')"
            wire:model="user.password_confirmation"
        />
    </form>
    @show
    @section('profile.notifications')
    <x-flux::table>
        <x-slot:title>
            <h2 class="pt-6 dark:text-white">{{ __('Notifications') }}</h2>
            <x-button color="indigo" x-on:click="initSW()">
                {{ __('Activate Web Push') }}
            </x-button>
        </x-slot>
        <x-slot:header>
            <th>{{ __('Notification') }}</th>
            <template
                x-for="(notificationChannel, name) in notificationChannels"
            >
                <th class="text-left">
                    <div x-text="name" />
                </th>
            </template>
        </x-slot>
        @foreach($notificationSettings as $notificationName => $notification)
            <tr>
                <td>
                    <div>{{ __(Str::of(class_basename($notificationName))->before('Notification')->headline()->toString()) }}</div>
                </td>
                @foreach($notification as $channel => $channelSettings)
                    <td>
                        <x-checkbox
                            wire:model.live="notificationSettings.{{ $notificationName }}.{{ $channel }}.is_active"
                        />
                    </td>
                @endforeach
            </tr>
        @endforeach
    </x-flux::table>
    @show
    @show
    <div class="flex justify-end space-x-5 pt-5">
        <x-button
            color="secondary"
            light
            :text="__('Cancel')"
            x-on:click="window.history.back()"
        />
        <x-button color="indigo" :text="__('Save')" wire:click="save" />
    </div>
</div>
