@if ($fields !== [])
    <div class="mt-4">
        <x-accordion>
            <x-accordion.items :title="$title">
                <div class="space-y-2">
                    @foreach ($fields as $field)
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <span
                                    class="font-medium"
                                    >{{ $field['label'] }}</span
                                >
                                <span class="text-sm text-gray-500">
                                    {{ $field['summary'] }}
                                </span>
                            </div>
                            @if ($field['is_overridden'])
                                <x-button
                                    :text="__('Set all to inherited')"
                                    color="secondary"
                                    flat
                                    sm
                                    wire:click="resetFields('{{ $field['name'] }}')"
                                    wire:flux-confirm.type.warning="{{ __('Reset the override on :field for every variant?', ['field' => $field['label']]) }}"
                                />
                            @endif
                        </div>
                    @endforeach
                </div>
            </x-accordion.items>
        </x-accordion>
    </div>
@endif
