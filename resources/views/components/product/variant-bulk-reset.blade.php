@if ($counters !== [])
    <x-card>
        <x-slot:header>
            {{ __('Effect on variants') }}
        </x-slot:header>
        <div class="space-y-2">
            @foreach ($counters as $field => $stat)
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <span class="font-medium">{{ __($field) }}</span>
                        <span class="text-sm text-gray-500">
                            {{ __(':inheriting of :total variants inherit this field', $stat) }}
                        </span>
                    </div>
                    @if ($stat['inheriting'] < $stat['total'])
                        <x-button
                            :text="__('Set all to inherited')"
                            color="secondary"
                            flat
                            sm
                            wire:click="resetFields('{{ $field }}')"
                            wire:flux-confirm.type.warning="{{ __('Reset the override on :field for every variant?', ['field' => __($field)]) }}"
                        />
                    @endif
                </div>
            @endforeach
        </div>
    </x-card>
@endif
