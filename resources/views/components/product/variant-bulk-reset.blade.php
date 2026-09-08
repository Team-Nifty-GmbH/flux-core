@php
    $overridden = collect($counters)
        ->filter(fn (array $stat): bool => $stat['inheriting'] < $stat['total'])
        ->count();
@endphp

@if ($counters !== [])
    <div class="mt-4">
        <x-accordion>
            <x-accordion.items
                :title="__(':overridden of :total fields overridden on at least one variant', ['overridden' => $overridden, 'total' => count($counters)])"
            >
                <div class="space-y-2">
                    @foreach ($counters as $field => $stat)
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <span class="font-medium">
                                    {{ __(\Illuminate\Support\Str::headline($field)) }}
                                </span>
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
                                    wire:flux-confirm.type.warning="{{ __('Reset the override on :field for every variant?', ['field' => __(\Illuminate\Support\Str::headline($field))]) }}"
                                />
                            @endif
                        </div>
                    @endforeach
                </div>
            </x-accordion.items>
        </x-accordion>
    </div>
@endif
