<div
    x-data="{
        addToast(event) {
            Alpine.$data(
                $el.querySelector('#toasts').querySelector('[x-data]'),
            ).add(event)
        },
        removeAll() {
            Alpine.$data(
                $el.querySelector('#toasts').querySelector('[x-data]'),
            ).toasts = []
        },
    }"
    x-init.once="
        window.Echo.private('{{ auth()->user()->receivesBroadcastNotificationsOn() }}').notification(
            (notification) => {
                $wire.sendNotify(notification);
            },
        )
    "
>
    <div>
        <div wire:click="showNotifications()">
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
                    </x-slot>
                </x-button.circle>
            </div>
        </div>
        <x-slide
            id="notifications-slide"
            scope="notifications"
            x-on:close="$wire.closeNotifications()"
        >
            <div class="flex h-full flex-col justify-between">
                <div
                    x-on:tallstackui:toast-list.window="addToast($event)"
                    x-on:tallstackui:toast.window="event.stopPropagation()"
                >
                    <div class="space-y-3 p-0" id="toasts">
                        <x-toast scope="relative" />
                        <div
                            x-intersect="show && $wire.showNotifications()"
                        ></div>
                    </div>
                </div>
            </div>
            <x-slot:footer>
                <div class="w-full p-6">
                    <x-button
                        color="indigo"
                        class="w-full"
                        :text="__('Mark all as read')"
                        x-on:click.prevent="$wire.markAllAsRead().then(() => removeAll())"
                    />
                </div>
            </x-slot>
        </x-slide>
    </div>
</div>
