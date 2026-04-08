<div
    x-data
    x-cloak
    x-show="$wire.show"
    class="relative"
    x-on:render-search-bar-widget.window="
        $wire.renderSearchBarWidget($event.detail.model, $event.detail.id)
    "
>
    @if($widgetComponent)
        <x-card card-classes="shadow-none">
            <livewire:is
                wire:key="search-bar-{{ $widgetComponent }}-{{ $widgetId }}"
                :component="$widgetComponent"
                :model-id="$widgetId"
            />
        </x-card>
    @endif
</div>
