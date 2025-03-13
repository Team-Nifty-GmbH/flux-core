<div
    class="fixed inset-0 z-30 overflow-y-auto p-4"
    {{ $attributes->only("x-show") }}
    x-cloak
>
    <div
        x-on:click="{{ $attributes->get("x-show") }} = false"
        class="fixed inset-0 transform bg-secondary-400 bg-opacity-60 transition-opacity dark:bg-secondary-700 dark:bg-opacity-60"
        {{ $attributes }}
        x-transition:enter="duration-300 ease-out"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="duration-200 ease-in"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
    ></div>
    <aside
        x-cloak
        {{ $attributes->merge(["class" => "fixed right-0 top-0 bottom-0 w-full sm:w-96 h-full backdrop-blur sm:backdrop-blur-none z-30 overflow-auto max-h-full soft-scrollbar shadow-md rounded-xl bg-white dark:bg-secondary-800"]) }}
        x-transition:enter="transform transition duration-500 ease-in-out"
        x-transition:enter-start="translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transform transition duration-500 ease-in-out"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="translate-x-full"
    >
        <div class="px-2 py-5">
            {{ $slot }}
        </div>
        <div
            class="absolute sticky bottom-0 w-full rounded-xl rounded-t-none border-t bg-secondary-50 px-4 py-4 sm:px-6 dark:border-secondary-600 dark:bg-secondary-800"
        >
            <div class="flex justify-end gap-x-4">
                {{ $footer ?? "" }}
            </div>
        </div>
    </aside>
</div>
