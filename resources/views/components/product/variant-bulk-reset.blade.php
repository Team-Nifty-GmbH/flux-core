@if(! empty($counters))
    <x-card>
        <x-slot:header>
            {{ __('Auswirkung auf Varianten') }}
        </x-slot:header>
        <div class="space-y-2">
            @foreach($counters as $field => $stat)
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <span class="font-medium">{{ __($field) }}</span>
                        <span class="text-sm text-gray-500">
                            {{ __(':inheriting von :total Varianten erben', $stat) }}
                        </span>
                    </div>
                    @if($stat['inheriting'] < $stat['total'])
                        <x-button
                            :text="__('Alle auf geerbt setzen')"
                            color="secondary"
                            flat
                            sm
                            wire:click="resetFieldOnAllVariants('{{ $field }}')"
                            wire:flux-confirm.type.warning="{{ __('Alle Variant-Overrides für :field zurücksetzen?', ['field' => __($field)]) }}"
                        />
                    @endif
                </div>
            @endforeach
        </div>
    </x-card>
@endif
