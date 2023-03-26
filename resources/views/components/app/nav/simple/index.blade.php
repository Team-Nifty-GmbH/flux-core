<div {{ $attributes->merge(['class' => 'flex flex-col border-r border-gray-200 pb-4 bg-white overflow-y-auto w-48 dark:bg-secondary-900 dark:border-secondary-700 dark:shadow-none']) }}>
    <div class="flex flex-grow flex-col">
        <nav class="flex-1 space-y-1" aria-label="Sidebar">
            {{ $slot }}
        </nav>
    </div>
</div>
