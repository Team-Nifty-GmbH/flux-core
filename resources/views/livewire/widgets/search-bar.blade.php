<div x-data
     x-cloak
     x-show="$wire.show"
     class="relative"
     x-on:render-search-bar-widget.window="$wire.renderSearchBarWidget($event.detail.model, $event.detail.id)"
>
    @if($widgetComponent)
        <x-flux::spinner />
        <x-card card-classes="shadow-none">
            <livewire:is
                wire:key="{{ uniqid() }}"
                :component="$widgetComponent"
                :model="$widgetModel"
                :model-id="$widgetId"
            />
        </x-card>
    @endif
</div>
