<!-- Tabs -->
<div class="pb-6" x-data="{active: '{{ $items[0]['view'] }}'}">
    <div class="sm:hidden">
        <label for="tabs" class="sr-only">{{ __('Select a tab') }}</label>
        <!-- Use an "onChange" listener to redirect the user to the selected tab URL. -->
        <x-select.styled
            wire:model.live="tab"
            class="mt-4 block w-full rounded-md border-gray-300 py-2 pl-3 pr-10 text-base focus:border-purple-500 focus:outline-none focus:ring-purple-500 sm:text-sm"
            :options="$items"
            select="label:view|value:label"
        />
    </div>
    <div class="hidden sm:block">
        <div class="border-b border-gray-200">
            <nav class="mt-2 -mb-px flex space-x-8" aria-label="Tabs">
                @foreach($items as $item)
                    <!-- Current: "border-purple-500 text-purple-600", Default: "border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-200" -->
                    <div wire:click="$set('tab', '{{ $item['view'] }}')"
                         x-on:click="active = '{{ $item['view'] }}'"
                         x-bind:class="active === '{{ $item['view']}}' ? 'border-purple-500 text-purple-600' : 'border-transparent text-gray-500'"
                         class="cursor-pointer whitespace-nowrap border-b-2 py-4 px-1 text-sm font-medium hover:border-gray-200 hover:text-gray-700">
                        {{ $item['label'] }}

                        <!-- Current: "bg-purple-100 text-purple-600", Default: "bg-gray-100 text-gray-900" -->
                        @if(array_key_exists('notifications', $item))
                            <span
                                x-bind:class="active === '{{ $item['view'] }}' ? 'bg-purple-100 text-purple-600' : 'bg-gray-100 text-gray-900'"
                                class="ml-2 hidden rounded-full py-0.5 px-2.5 text-xs font-medium md:inline-block">{{ $item['notifications'] }}</span>
                        @endif
                    </div>
                @endforeach
            </nav>
        </div>
    </div>
</div>
{{ $slot }}
