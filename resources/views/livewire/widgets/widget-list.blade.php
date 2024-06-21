<div>
    <h1 class="mb-2">Available Widgets</h1>
    @forelse($availableWidgets as $widget)
    <div x-on:click="selectWidget('{{$widget['component_name']}}','{{$id}}')" class="w-full cursor-pointer mb-2 p-2 border rounded hover:opacity-100">
        {{$widget['label']}}
    </div>
        @empty
        <div>empty</div>
    @endforelse
</div>
