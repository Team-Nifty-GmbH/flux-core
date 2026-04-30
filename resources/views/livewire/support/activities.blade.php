<div
    x-data="{
        activeActivity: null,
        showProperties(id) {
            this.activeActivity = this.activeActivity === id ? null : id;
        },
    }"
>
    <x-timeline>
        @island(name: 'activities')
            @foreach($activities as $activity)
                <x-timeline.items
                    wire:key="activity-{{ data_get($activity, 'id') }}"
                    :date="data_get($activity, 'created_at_formatted')"
                    :title="trim(data_get($activity, 'causer.name') . ' ' . data_get($activity, 'event'))"
                    :description="data_get($activity, 'description') !== data_get($activity, 'event') ? data_get($activity, 'description') : null"
                >
                    <x-slot:marker>
                        <x-avatar
                            xs
                            :image="data_get($activity, 'causer.avatar_url')"
                        />
                    </x-slot:marker>
                    @if(! empty(data_get($activity, 'properties.attributes', [])))
                        <button
                            type="button"
                            x-on:click="showProperties({{ data_get($activity, 'id') }})"
                            class="text-primary-600 dark:text-primary-400 mt-1 cursor-pointer appearance-none border-0 bg-transparent p-0 text-xs hover:underline"
                        >
                            <span
                                x-show="activeActivity !== {{ data_get($activity, 'id') }}"
                                x-cloak
                                >{{ __('Show changes') }}</span
                            >
                            <span
                                x-show="activeActivity === {{ data_get($activity, 'id') }}"
                                x-cloak
                                >{{ __('Hide changes') }}</span
                            >
                        </button>
                        <div
                            x-show="activeActivity === {{ data_get($activity, 'id') }}"
                            x-collapse
                            x-cloak
                            class="mt-1 text-xs text-gray-600 dark:text-gray-300"
                        >
                            @foreach(data_get($activity, 'properties.attributes', []) as $name => $value)
                                <div>
                                    <span class="font-semibold"
                                        >{{ $name }}:</span
                                    >
                                    @php($old = data_get($activity, 'properties.old.' . $name))
                                    @if(! is_null($old))
                                        <span
                                            >{{ is_scalar($old) ? $old : json_encode($old) }}</span
                                        >
                                        <span> -&gt; </span>
                                    @endif
                                    <span
                                        >{{ is_scalar($value) ? $value : json_encode($value) }}</span
                                    >
                                </div>
                            @endforeach
                        </div>
                    @endif
                </x-timeline.items>
            @endforeach
        @endisland
    </x-timeline>

    <div x-show="$wire.page * $wire.perPage < $wire.total" x-cloak class="pt-2">
        <x-button
            type="button"
            wire:click="loadMore()"
            wire:intersect="loadMore()"
            wire:island.append="activities"
            class="w-full"
            :text="__('Show more')"
            loading="loadMore"
        />
    </div>
</div>
