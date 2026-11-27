<div class="space-y-4">
    <form
        id="passkeyForm"
        wire:submit="validatePasskeyProperties"
        class="flex items-end gap-2"
    >
        <div class="grow">
            <x-input
                wire:model="name"
                :label="__('passkeys::passkeys.name')"
                autocomplete="off"
            />
        </div>
        <x-button
            type="submit"
            color="primary"
            :text="__('passkeys::passkeys.create')"
        />
    </form>

    <ul class="space-y-2">
        @foreach ($passkeys as $passkey)
            <li
                class="dark:bg-dark-700 flex items-center justify-between gap-4 rounded-lg bg-gray-100 p-4"
            >
                <div class="dark:text-dark-100 font-medium text-gray-700">
                    {{ $passkey->name }}
                </div>
                <div class="dark:text-dark-400 grow text-sm text-gray-500">
                    {{ __('passkeys::passkeys.last_used') }}: {{ $passkey->last_used_at?->diffForHumans() ?? __('passkeys::passkeys.not_used_yet') }}
                </div>
                <x-button
                    color="red"
                    sm
                    :text="__('passkeys::passkeys.delete')"
                    wire:click="deletePasskey({{ $passkey->getKey() }})"
                />
            </li>
        @endforeach
    </ul>
</div>

@include('passkeys::livewire.partials.createScript')
