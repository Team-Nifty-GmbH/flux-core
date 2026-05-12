<x-flux::layouts.app>
    <x-slot:title>
        {{ __('Register Passkey') }}
    </x-slot:title>
    <div
        class="flex min-h-full flex-col justify-center py-12 sm:px-6 lg:px-8"
        x-data="{
            state: 'naming',
            name: '{{ __('Mobile Device') }}',
            error: null,
            async run() {
                if (! this.name) {
                    return;
                }
                if (typeof window.startRegistration !== 'function') {
                    this.state = 'error';
                    this.error = '{{ __('Passkey is not supported in this environment.') }}';
                    return;
                }
                this.state = 'starting';
                try {
                    const optionsJSON = JSON.parse(@js($options));
                    const response = await window.startRegistration({ optionsJSON });
                    this.state = 'submitting';
                    const r = await fetch(@js($finishUrl), {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        },
                        body: JSON.stringify({
                            code: @js($code),
                            transfer_token: @js($transferToken),
                            name: this.name,
                            response: JSON.stringify(response),
                        }),
                    });
                    if (! r.ok) {
                        const data = await r.json().catch(() => ({}));
                        throw new Error(data.statusMessage || 'finish_failed');
                    }
                    const { data: { redirect } = {} } = await r.json();
                    if (! redirect) {
                        throw new Error('missing_redirect');
                    }
                    window.location.href = redirect;
                } catch (e) {
                    this.state = 'error';
                    this.error = e?.message || '{{ __('Passkey registration failed.') }}';
                }
            },
        }"
    >
        <div class="sm:mx-auto sm:w-full sm:max-w-md">
            <x-flux::logo fill="#0690FA" class="h-24" />
        </div>
        <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
            <x-card>
                <div class="space-y-4 py-6">
                    <template x-if="state === 'naming'">
                        <div class="space-y-4">
                            <div class="text-center">
                                <x-icon
                                    name="finger-print"
                                    class="mx-auto size-12 text-indigo-600"
                                />
                                <p class="mt-4 text-base font-medium">{{ __('Register a new passkey') }}</p>
                                <p class="mt-2 text-sm text-gray-500">{{ __('Choose a name for this device, then confirm with biometrics.') }}</p>
                            </div>
                            <x-input
                                :label="__('Device name')"
                                x-model="name"
                                maxlength="255"
                                required
                            />
                            <x-button
                                :text="__('Continue')"
                                color="indigo"
                                class="w-full"
                                x-on:click="run()"
                            />
                            <x-button
                                :text="__('Cancel')"
                                color="secondary"
                                flat
                                class="w-full"
                                :href="$cancelRedirect"
                            />
                        </div>
                    </template>
                    <template
                        x-if="state === 'starting' || state === 'submitting'"
                    >
                        <div class="text-center">
                            <x-icon
                                name="arrow-path"
                                class="mx-auto size-12 animate-spin text-indigo-600"
                            />
                            <p
                                class="mt-4 text-base font-medium"
                                x-text="state === 'starting'
                                ? '{{ __('Confirm with biometrics…') }}'
                                : '{{ __('Storing passkey…') }}'"
                            ></p>
                        </div>
                    </template>
                    <template x-if="state === 'error'">
                        <div class="text-center">
                            <x-icon
                                name="exclamation-triangle"
                                class="mx-auto size-12 text-red-600"
                            />
                            <p
                                class="mt-4 text-base font-medium"
                                x-text="error"
                            ></p>
                            <div class="mt-4 flex flex-col gap-2">
                                <x-button
                                    :text="__('Try again')"
                                    color="indigo"
                                    x-on:click="
                                        state = 'naming';
                                        error = null;
                                    "
                                />
                                <x-button
                                    :text="__('Cancel')"
                                    color="secondary"
                                    flat
                                    :href="$cancelRedirect"
                                />
                            </div>
                        </div>
                    </template>
                </div>
            </x-card>
        </div>
    </div>
</x-flux::layouts.app>
