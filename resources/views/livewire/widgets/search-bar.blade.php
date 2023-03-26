<div x-data="{showWidget: $wire.entangle('show')}"
     x-show="showWidget"
     class="relative"
     x-on:render-search-bar-widget.window="$wire.renderSearchBarWidget($event.detail.model, $event.detail.id)"
>
    @if($widgetComponent)
        <x-spinner />
        <x-card card-classes="shadow-none">
            <livewire:is wire:key="{{ uniqid() }}" :component="$widgetComponent" :model="$widgetModel" :model-id="$widgetId" />
        </x-card>
    @endif
</div>
