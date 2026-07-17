<div
    x-data="{
        name: '',
        state: 'idle',
        error: null,
        async register() {
            if (! this.name.trim()) {
                this.error = '{{ __('Please enter a name.') }}';
                this.state = 'error';
                return;
            }
            if (! window.nuxbeAppBridge?.passkeyRegister) {
                this.error = '{{ __('App bridge unavailable.') }}';
                this.state = 'error';
                return;
            }
            this.state = 'pending';
            this.error = null;
            try {
                const result = await window.nuxbeAppBridge.passkeyRegister();
                if (result?.success) {
                    this.state = 'idle';
                    this.name = '';
                    Livewire.dispatch('passkey-registered');
                } else {
                    this.error = result?.error || '{{ __('Passkey registration failed.') }}';
                    this.state = 'error';
                }
            } catch (e) {
                this.error = e?.message || '{{ __('Passkey registration failed.') }}';
                this.state = 'error';
            }
        },
    }"
>
    <div class="space-y-3">
        <div class="flex items-end gap-2">
            <div class="flex-1">
                <x-input
                    :label="__('Device name')"
                    x-model="name"
                    maxlength="255"
                />
            </div>
            <x-button
                :text="__('Create passkey')"
                color="primary"
                x-on:click="register()"
                x-bind:disabled="state === 'pending'"
            />
        </div>
        <template x-if="state === 'pending'">
            <p class="text-sm text-gray-500">
                {{ __('Confirm with biometrics in the system browser.') }}
            </p>
        </template>
        <template x-if="state === 'error'">
            <p class="text-sm text-red-600" x-text="error"></p>
        </template>
    </div>
</div>
