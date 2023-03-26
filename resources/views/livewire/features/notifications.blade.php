<div
    x-data="wireui_notifications"
>
    <div x-data="{
         unread: @entangle('unread'),
         showNotifications: @entangle('showNotifications'),
         closeNotification(notification) {
             $wire.markAsRead(notification.notification_id);

             this.notifications = this.notifications.filter(n => n.id !== notification.id);
         },
     }"
         x-on:keydown.escape="showNotifications = false"
         x-on:click.away="showNotifications = false"
    >
        <div>
            <x-button.circle
                primary
                icon="bell"
                x-on:click="
                $wire.getNotification().then(
                    result => {
                        result.forEach(
                            notification => {
                                addConfirmNotification({options: notification, componentId: '{{ $this->id }}'
                            }
                        )
                    }
                )});
                showNotifications = true;"
            />
            <div class="z-10 -mt-11 pl-5" x-cloak x-transition x-show="unread">
                <x-button rounded 2xs negative >
                    <x-slot name="label">
                        <span x-text="unread"></span>
                    </x-slot>
                </x-button>
            </div>
        </div>
        <aside
            x-cloak
            x-show="showNotifications"
            x-transition:enter="transform transition ease-in-out duration-500"
            x-transition:enter-start="translate-x-full"
            x-transition:enter-end="translate-x-0"
            x-transition:leave="transform transition ease-in-out duration-500"
            x-transition:leave-start="translate-x-0"
            x-transition:leave-end="translate-x-full"
            class="soft-scrollbar fixed right-0 top-0 bottom-0 z-30 h-full max-h-full w-full overflow-auto backdrop-blur sm:w-96 sm:backdrop-blur-none"
        >
            <div class="flex h-full flex-col justify-between">
                <div>
                    <div class="flex justify-end p-2.5">
                        <x-button.circle secondary icon="x" x-on:click.prevent="showNotifications = false, notifications = []" />
                    </div>
                    <div class="space-y-3 p-6">
                        <template x-for="notification in notifications">
                            <div class="dark:bg-secondary-800 dark:border-secondary-700 pointer-events-auto relative w-full max-w-sm overflow-hidden rounded-lg bg-white shadow-lg ring-1 ring-black ring-opacity-5 dark:border"
                                 :class="{ 'flex': notification.rightButtons }"
                                 :id="`notification.${notification.id}`"
                            >
                                <div
                                    :class="{
                                        'pl-4': Boolean(notification.dense),
                                        'p-4': !Boolean(notification.rightButtons),
                                        'w-0 flex-1 flex items-center p-4': Boolean(notification.rightButtons),
                                    }"
                                >
                                    <div
                                        :class="{
                                            'flex items-start': !Boolean(notification.rightButtons),
                                            'w-full flex': Boolean(notification.rightButtons),
                                        }"
                                    >
                                        <!-- notification icon|img -->
                                        <template x-if="notification.icon || notification.img">
                                            <div class="shrink-0"
                                                 :class="{
                                                    'w-6': Boolean(notification.icon),
                                                    'pt-0.5': Boolean(notification.img),
                                                }"
                                            >
                                                <template x-if="notification.icon">
                                                    <div class="notification-icon"></div>
                                                </template>

                                                <template x-if="notification.img">
                                                    <img class="h-10 w-10 rounded-full" :src="notification.img" />
                                                </template>
                                            </div>
                                        </template>

                                        <div class="w-0 flex-1 pt-0.5"
                                             :class="{
                                                'ml-3': Boolean(notification.icon || notification.img)
                                             }"
                                        >
                                            <p class="text-secondary-900 text-sm font-medium dark:text-gray-50"
                                               x-show="notification.title"
                                               x-html="notification.title">
                                            </p>
                                            <p class="text-secondary-500 mt-1 text-sm"
                                               x-show="notification.description"
                                               x-html="notification.description">
                                            </p>

                                            <!-- actions buttons -->
                                            <template x-if="!notification.dense && !notification.rightButtons && (notification.accept || notification.reject)">
                                                <div class="mt-3 flex gap-x-3">
                                                    <button class="rounded-md text-sm font-medium focus:outline-none"
                                                            :class="{
                                                                'bg-white dark:bg-transparent text-primary-600 hover:text-primary-500': !Boolean($wireui.dataGet(notification, 'accept.style')),
                                                                [$wireui.dataGet(notification, 'accept.style')]: Boolean($wireui.dataGet(notification, 'accept.style')),
                                                                'px-3 py-2 border shadow-sm': Boolean($wireui.dataGet(notification, 'accept.solid')),
                                                            }"
                                                            x-on:click="accept(notification)"
                                                            x-show="$wireui.dataGet(notification, 'accept.label')"
                                                            x-text="$wireui.dataGet(notification, 'accept.label', '')">
                                                    </button>

                                                    <button class="rounded-md text-sm font-medium focus:outline-none"
                                                            :class="{
                                                                'bg-white dark:bg-transparent text-secondary-700 dark:text-secondary-600 hover:text-secondary-500': !Boolean($wireui.dataGet(notification, 'reject.style')),
                                                                [$wireui.dataGet(notification, 'reject.style')]: Boolean($wireui.dataGet(notification, 'reject.style')),
                                                                'px-3 py-2 border border-secondary-300 shadow-sm': Boolean($wireui.dataGet(notification, 'accept.solid')),
                                                            }"
                                                            x-on:click="reject(notification)"
                                                            x-show="$wireui.dataGet(notification, 'reject.label')"
                                                            x-text="$wireui.dataGet(notification, 'reject.label', '')"
                                                            x-init="$wireui.dataGet(notification, 'accept.label')">
                                                    >
                                                    </button>
                                                </div>
                                            </template>
                                        </div>

                                        <div class="ml-4 flex shrink-0">
                                            <!-- accept button -->
                                            <button class="mr-4 shrink-0 rounded-md text-sm font-medium focus:outline-none"
                                                    :class="{
                                                        'text-primary-600 hover:text-primary-500': !Boolean($wireui.dataGet(notification, 'accept.style')),
                                                        [$wireui.dataGet(notification, 'accept.style')]: Boolean($wireui.dataGet(notification, 'accept.style'))
                                                    }"
                                                    x-on:click="accept(notification)"
                                                    x-show="notification.dense && notification.accept"
                                                    x-text="$wireui.dataGet(notification, 'accept.label', '')">
                                            </button>

                                            <!-- close button -->
                                            <button class="text-secondary-400 hover:text-secondary-500 inline-flex rounded-md focus:outline-none"
                                                    x-on:click="closeNotification(notification)">
                                                <x-dynamic-component
                                                    :component="WireUi::component('icon')"
                                                    class="h-5 w-5"
                                                    name="x"
                                                />
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- right actions buttons -->
                                <template x-if="notification.rightButtons">
                                    <div class="border-secondary-200 dark:border-secondary-700 flex flex-col border-l">
                                        <template x-if="notification.accept">
                                            <div class="flex h-0 flex-1"
                                                 :class="{
                                                    'border-b border-secondary-200 dark:border-secondary-700': notification.reject
                                                }"
                                            >
                                                <button class="flex w-full items-center justify-center rounded-none rounded-tr-lg px-4 py-3 text-sm font-medium focus:outline-none"
                                                        :class="{
                                                            'text-primary-600 hover:text-primary-500 hover:bg-secondary-50 dark:hover:bg-secondary-700': !Boolean(notification.accept.style),
                                                            [notification.accept.style]: Boolean(notification.accept.style),
                                                            'rounded-br-lg': !Boolean(notification.reject),
                                                        }"
                                                        x-on:click="accept(notification)"
                                                        x-text="notification.accept.label">
                                                </button>
                                            </div>
                                        </template>

                                        <template x-if="notification.reject">
                                            <div class="flex h-0 flex-1">
                                                <button class="flex w-full items-center justify-center rounded-none rounded-br-lg px-4 py-3 text-sm font-medium focus:outline-none"
                                                        :class="{
                                                            'text-secondary-700 hover:text-secondary-500 dark:text-secondary-600 hover:bg-secondary-50 dark:hover:bg-secondary-700': !Boolean(notification.reject.style),
                                                            [notification.reject.style]: Boolean(notification.reject.style),
                                                            'rounded-tr-lg': !Boolean(notification.accept),
                                                        }"
                                                        x-on:click="reject(notification)"
                                                        x-text="notification.reject.label">
                                                </button>
                                            </div>
                                        </template>
                                    </div>
                                </template>
                            </div>
                        </template>
                    </div>
                </div>
                <div class="w-full p-6">
                    <x-button primary class="w-full" :label="__('Mark all as read')" x-on:click.prevent="$wire.markAllAsRead(), showNotifications = false"/>
                </div>
            </div>
        </aside>
    </div>
</div>
