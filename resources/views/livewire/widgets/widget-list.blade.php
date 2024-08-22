<div class="h-full p-2.5  text-gray-400">
    <h1 class="mb-4 mt-2 text-2xl font-medium">{{ __('Available Widgets') }}</h1>
    @forelse($availableWidgets as $widget)
        <div x-on:click="selectWidget('{{ $widget['component_name'] }}','{{ $id }}')"
             class="w-full cursor-pointer mb-2 p-2 border rounded hover:opacity-100">
            {{ __($widget['label']) }}
        </div>
    @empty
        <div class="h-full mx-auto flex flex-col justify-center items-center">
            <h2 class="text-2xl font-medium">{{ __('No widgets available') }}</h2>
        </div>
    @endforelse
</div>