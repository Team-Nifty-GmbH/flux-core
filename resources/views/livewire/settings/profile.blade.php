<div
    class="p-10"
    x-data="{
        webPushSupport: {},
        async checkSupport() {
            if (window.webPush) {
                const support = await window.webPush.checkWebPushSupport()
                support.allSupported =
                    support.serviceWorker &&
                    support.pushManager &&
                    support.notification &&
                    support.https
                this.webPushSupport = support
            }
        },
        init() {
            this.checkSupport()
            window.addEventListener('focus', () => this.checkSupport())
        },
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

    @section('profile.web-push')
    <div class="space-y-6 pt-8">
        <x-card>
            <div class="space-y-4">
                <div
                    class="flex items-center justify-between border-b pb-4 dark:border-gray-700"
                >
                    <div>
                        <h3 class="text-lg font-semibold dark:text-white">
                            {{ __('Web Push Notifications') }}
                        </h3>
                        <p
                            class="mt-1 text-sm text-gray-600 dark:text-gray-400"
                        >
                            {{ __('Receive notifications directly in your browser') }}
                        </p>
                    </div>
                    <div class="flex items-center space-x-2">
                        <div
                            x-show="webPushSupport.allSupported && !webPushSupport.currentBrowserSubscribed"
                            x-cloak
                        >
                            <x-button
                                :text="__('Activate')"
                                color="primary"
                                x-on:click="WebPush.initSW().then(() => checkSupport()).catch(error => console.error(error))"
                                icon="bell"
                            />
                        </div>
                        <div
                            x-show="webPushSupport.allSupported && webPushSupport.currentBrowserSubscribed"
                            x-cloak
                            class="flex items-center gap-2"
                        >
                            <x-badge
                                color="emerald"
                                :text="__('This browser is activated')"
                            />
                            <x-button
                                :text="__('Reactivate')"
                                color="secondary"
                                size="sm"
                                x-on:click="WebPush.initSW(true).then(() => checkSupport()).catch(error => console.error(error))"
                                icon="arrow-path"
                            />
                        </div>
                    </div>
                </div>

                <div>
                    <h4
                        class="mb-3 text-sm font-medium text-gray-700 dark:text-gray-300"
                    >
                        {{ __('Requirements') }}
                    </h4>
                    <div class="space-y-2 text-sm">
                        <div class="flex items-center space-x-3">
                            <span class="relative flex size-3">
                                <span
                                    class="absolute inline-flex h-full w-full animate-ping rounded-full opacity-75"
                                    x-bind:class="webPushSupport.https ? 'bg-green-400' : 'bg-red-400'"
                                ></span>
                                <span
                                    class="relative inline-flex size-3 rounded-full"
                                    x-bind:class="webPushSupport.https ? 'bg-green-500' : 'bg-red-500'"
                                ></span>
                            </span>
                            <span
                                x-bind:class="
                                    webPushSupport.https
                                        ? 'text-gray-700 dark:text-gray-300'
                                        : 'text-gray-500 dark:text-gray-400'
                                "
                            >
                                {{ __('Secure connection') }}
                                <span
                                    x-show="!webPushSupport.https"
                                    class="text-xs text-gray-500 dark:text-gray-500"
                                >
                                    ({{ __('HTTPS required') }})
                                </span>
                            </span>
                        </div>

                        <div class="flex items-center space-x-3">
                            <span class="relative flex size-3">
                                <span
                                    class="absolute inline-flex h-full w-full animate-ping rounded-full opacity-75"
                                    x-bind:class="webPushSupport.serviceWorker ? 'bg-green-400' : 'bg-red-400'"
                                ></span>
                                <span
                                    class="relative inline-flex size-3 rounded-full"
                                    x-bind:class="webPushSupport.serviceWorker ? 'bg-green-500' : 'bg-red-500'"
                                ></span>
                            </span>
                            <span
                                x-bind:class="
                                    webPushSupport.serviceWorker
                                        ? 'text-gray-700 dark:text-gray-300'
                                        : 'text-gray-500 dark:text-gray-400'
                                "
                            >
                                {{ __('Service Workers support') }}
                            </span>
                        </div>

                        <div class="flex items-center space-x-3">
                            <span class="relative flex size-3">
                                <span
                                    class="absolute inline-flex h-full w-full animate-ping rounded-full opacity-75"
                                    x-bind:class="webPushSupport.pushManager ? 'bg-green-400' : 'bg-red-400'"
                                ></span>
                                <span
                                    class="relative inline-flex size-3 rounded-full"
                                    x-bind:class="webPushSupport.pushManager ? 'bg-green-500' : 'bg-red-500'"
                                ></span>
                            </span>
                            <span
                                x-bind:class="
                                    webPushSupport.pushManager
                                        ? 'text-gray-700 dark:text-gray-300'
                                        : 'text-gray-500 dark:text-gray-400'
                                "
                            >
                                {{ __('Push API support') }}
                            </span>
                        </div>

                        <div class="flex items-center space-x-3">
                            <span class="relative flex size-3">
                                <span
                                    class="absolute inline-flex h-full w-full animate-ping rounded-full opacity-75"
                                    x-bind:class="webPushSupport.notification ? 'bg-green-400' : 'bg-red-400'"
                                ></span>
                                <span
                                    class="relative inline-flex size-3 rounded-full"
                                    x-bind:class="webPushSupport.notification ? 'bg-green-500' : 'bg-red-500'"
                                ></span>
                            </span>
                            <span
                                x-bind:class="
                                    webPushSupport.notification
                                        ? 'text-gray-700 dark:text-gray-300'
                                        : 'text-gray-500 dark:text-gray-400'
                                "
                            >
                                {{ __('Notification API support') }}
                            </span>
                        </div>

                        <div class="flex items-center space-x-3">
                            <span class="relative flex size-3">
                                <span
                                    class="absolute inline-flex h-full w-full animate-ping rounded-full opacity-75"
                                    x-bind:class="$wire.webPushSupport.vapidKey ? 'bg-green-400' : 'bg-red-400'"
                                ></span>
                                <span
                                    class="relative inline-flex size-3 rounded-full"
                                    x-bind:class="$wire.webPushSupport.vapidKey ? 'bg-green-500' : 'bg-red-500'"
                                ></span>
                            </span>
                            <span
                                x-bind:class="
                                    $wire.webPushSupport.vapidKey
                                        ? 'text-gray-700 dark:text-gray-300'
                                        : 'text-gray-500 dark:text-gray-400'
                                "
                            >
                                {{ __('VAPID keys') }}
                            </span>
                        </div>

                        <div
                            class="flex items-center space-x-3"
                            x-cloak
                            x-show="$wire.webPushSupport.isSafari"
                        >
                            <span class="relative flex size-3">
                                <span
                                    x-bind:class="$wire.webPushSupport.vapidSubject ? 'bg-green-400' : 'bg-red-400'"
                                    class="absolute inline-flex h-full w-full animate-ping rounded-full opacity-75"
                                ></span>
                                <span
                                    x-bind:class="$wire.webPushSupport.vapidSubject ? 'bg-green-500' : 'bg-red-500'"
                                    class="relative inline-flex size-3 rounded-full"
                                ></span>
                            </span>
                            <span
                                x-bind:class="
                                    $wire.webPushSupport.vapidSubject
                                        ? 'text-gray-700 dark:text-gray-300'
                                        : 'text-gray-500 dark:text-gray-400'
                                "
                            >
                                {{ __('VAPID Subject (Safari)') }}
                                <span
                                    x-cloak
                                    x-show="! $wire.webPushSupport.vapidSubject"
                                    class="text-xs text-red-600 dark:text-red-400"
                                >
                                    ({{ __('must start with mailto: or https://') }})
                                </span>
                                <span
                                    x-cloak
                                    x-show="$wire.webPushSupport.vapidSubject"
                                    class="text-xs text-green-600 dark:text-green-400"
                                >
                                    ✓
                                </span>
                            </span>
                        </div>

                        <div
                            class="flex items-center space-x-3"
                            x-show="webPushSupport.allSupported"
                            x-cloak
                        >
                            <span class="relative flex size-3">
                                <span
                                    class="absolute inline-flex h-full w-full animate-ping rounded-full opacity-75"
                                    x-bind:class="webPushSupport.currentBrowserSubscribed ? 'bg-blue-500' : 'bg-gray-400'"
                                ></span>
                                <span
                                    class="relative inline-flex size-3 rounded-full"
                                    x-bind:class="webPushSupport.currentBrowserSubscribed ? 'bg-blue-500' : 'bg-gray-400'"
                                ></span>
                            </span>
                            <span
                                x-bind:class="
                                    webPushSupport.currentBrowserSubscribed
                                        ? 'text-gray-700 dark:text-gray-300'
                                        : 'text-gray-500 dark:text-gray-400'
                                "
                            >
                                {{ __('Current browser') }}
                                <span
                                    x-show="webPushSupport.currentBrowserSubscribed"
                                    class="text-xs text-blue-600 dark:text-blue-400"
                                >
                                    ({{ __('Activated') }})
                                </span>
                                <span
                                    x-show="!webPushSupport.currentBrowserSubscribed"
                                    class="text-xs text-gray-500 dark:text-gray-500"
                                >
                                    ({{ __('Not activated') }})
                                </span>
                            </span>
                        </div>
                    </div>
                </div>

                <div
                    x-cloak
                    x-show="($wire.pushSubscriptions?.length ?? []) > 0"
                >
                    <h4
                        class="mb-3 text-sm font-medium text-gray-700 dark:text-gray-300"
                    >
                        {{ __('Active Subscriptions') }}
                    </h4>
                    <x-table>
                        <x-slot:header>
                            <th>{{ __('Browser') }}</th>
                            <th>{{ __('Activated') }}</th>
                            <th class="text-right">{{ __('Actions') }}</th>
                        </x-slot>
                        <template
                            x-for="subscription in $wire.pushSubscriptions"
                        >
                            <x-table.row
                                x-data="{ isCurrentBrowser: false }"
                                x-init="
                                    $nextTick(async () => {
                                        if (window.webPush) {
                                            isCurrentBrowser = await window.webPush.checkCurrentSubscription(subscription.endpoint);
                                        }
                                    })
                                "
                            >
                                <td>
                                    <div class="flex items-center">
                                        <x-icon
                                            name="computer-desktop"
                                            class="mr-2 size-8"
                                        />
                                        <span
                                            x-text="subscription.browser"
                                        ></span>
                                        <span
                                            x-show="isCurrentBrowser"
                                            x-cloak
                                            class="relative ml-2 flex size-3"
                                        >
                                            <span
                                                class="absolute inline-flex h-full w-full animate-ping rounded-full bg-green-400 opacity-75"
                                            ></span>
                                            <span
                                                class="relative inline-flex size-3 rounded-full bg-green-500"
                                            ></span>
                                        </span>
                                    </div>
                                </td>
                                <td
                                    x-text="window.formatters.datetime(subscription.created_at)"
                                ></td>
                                <td class="text-right">
                                    <x-button
                                        color="red"
                                        size="xs"
                                        icon="trash"
                                        wire:flux-confirm.type.error="{{ __('wire:confirm.delete', ['model' => __('Push Subscription')]) }}"
                                        wire:click="deletePushSubscription(subscription.id)"
                                    />
                                </td>
                            </x-table.row>
                        </template>
                    </x-table>
                </div>
                <div
                    x-show="webPushSupport.allSupported && ! ($wire.pushSubscriptions?.length ?? []) > 0"
                    x-cloak
                >
                    <div class="py-8 text-center">
                        <x-icon
                            name="bell-slash"
                            class="mx-auto size-12 text-gray-400"
                        />
                        <h3
                            class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100"
                        >
                            {{ __('No active subscriptions') }}
                        </h3>
                        <p
                            class="mt-1 text-sm text-gray-500 dark:text-gray-400"
                        >
                            {{ __('Get started by clicking the activate button above.') }}
                        </p>
                    </div>
                </div>
            </div>
            <x-slot:footer>
                <div
                    class="flex justify-end"
                    x-cloak
                    x-show="($wire.pushSubscriptions?.length ?? []) > 0"
                >
                    <x-button
                        :text="__('Send Test')"
                        color="secondary"
                        wire:click="sendTestNotification"
                        icon="paper-airplane"
                    />
                </div>
            </x-slot>
        </x-card>
    </div>
    @show

    @section('profile.notifications')
    <x-flux::table>
        <x-slot:title>
            <h2 class="pt-6 dark:text-white">{{ __('Notifications') }}</h2>
        </x-slot>
        <x-slot:header>
            <th>{{ __('Notification') }}</th>
            <template
                x-for="(notificationChannel, name) in $wire.notificationChannels"
            >
                <th class="text-left">
                    <div x-text="name" />
                </th>
            </template>
        </x-slot>
        <template
            x-for="(notification, notificationName) in $wire.notificationSettings"
        >
            <tr>
                <td class="text-center">
                    <div x-text="$wire.notifications[notificationName]" />
                </td>
                <template x-for="(channelSettings, channel) in notification">
                    <td>
                        <x-checkbox
                            x-model="$wire.notificationSettings[notificationName][channel].is_active"
                        />
                    </td>
                </template>
            </tr>
        </template>
    </x-flux::table>
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
    @show
</div>
