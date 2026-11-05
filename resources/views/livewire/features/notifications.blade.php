<div
    x-data
    x-init.once="
        window.Echo.private(
            '{{ auth()->user()->receivesBroadcastNotificationsOn() }}',
        ).notification((notification) => {
            $wire.sendNotify(notification)
        })
    "
>
    <div>
        <div x-on:click="$tsui.open.slide('notifications-slide')">
            <x-button.circle color="indigo" icon="bell" />
            <div
                class="z-10 -mt-11 pl-5"
                x-cloak
                x-transition
                x-show="$wire.unread"
            >
                <x-button.circle sm color="red">
                    <x-slot:text>
                        <span x-text="$wire.unread"></span>
                    </x-slot:text>
                </x-button.circle>
            </div>
        </div>
        <x-slide
            id="notifications-slide"
            scope="notifications"
            x-on:close="
                $wire
                    .closeNotifications()
                    .then(() => $dispatch('notifications:clear'))
            "
        >
            <div
                x-data="{
                    refill: null,
                    addToast(event) {
                        const list = $tallstackuiToast($refs.toasts.id);
                        list.alpineComponent.add(event);

                        if (list.element.parentElement !== $refs.toasts) {
                            $refs.toasts.prepend(list.element);
                        }

                        clearTimeout(this.refill);
                        this.refill = setTimeout(() => {
                            if (
                                show &&
                                $wire.hasMore &&
                                $refs.more.getBoundingClientRect().top <
                                    window.innerHeight
                            ) {
                                $wire.showNotifications();
                            }
                        }, 100);
                    },
                    removeAll() {
                        $tallstackuiToast(
                            $refs.toasts.id,
                        ).alpineComponent.toasts = [];
                    },
                }"
                class="flex h-full flex-col justify-between"
                x-on:ts-ui:toast-list.window="addToast($event)"
                x-on:ts-ui:toast.window="event.stopImmediatePropagation()"
                x-on:notifications:clear.window="removeAll()"
            >
                <div
                    id="notifications-toast-list"
                    class="space-y-3 p-0"
                    x-ref="toasts"
                    wire:ignore
                >
                    <x-toast scope="relative" />
                    <div
                        x-ref="more"
                        x-intersect="show && $wire.showNotifications()"
                    ></div>
                </div>
            </div>
            <x-slot:footer>
                <div class="w-full p-6">
                    <x-button
                        color="indigo"
                        class="w-full"
                        :text="__('Mark all as read')"
                        loading="markAllAsRead"
                        x-on:click.prevent="
                            $wire
                                .markAllAsRead()
                                .then(() => $dispatch('notifications:clear'))
                        "
                    />
                </div>
            </x-slot:footer>
        </x-slide>
    </div>
</div>
