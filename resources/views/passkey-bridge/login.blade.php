<x-flux::layouts.app>
    <x-slot:title>
        {{ __('Passkey Login') }}
    </x-slot:title>
    <div
        class="flex min-h-full flex-col justify-center py-12 sm:px-6 lg:px-8"
        x-data="{
            state: 'starting',
            error: null,
            async run() {
                if (typeof window.startAuthentication !== 'function') {
                    this.state = 'error';
                    this.error = '{{ __('Passkey is not supported in this environment.') }}';
                    return;
                }
                try {
                    const optionsJSON = JSON.parse(@js($options));
                    const response = await window.startAuthentication({ optionsJSON });
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
                            response: JSON.stringify(response),
                        }),
                    });
                    if (! r.ok) {
                        const data = await r.json().catch(() => ({}));
                        throw new Error(data.statusMessage || 'finish_failed');
                    }
                    const { data: { redirect } = {} } = await r.json();
                    window.location.href = redirect;
                } catch (e) {
                    this.state = 'error';
                    this.error = e?.message || '{{ __('Passkey authentication failed.') }}';
                }
            },
        }"
        x-init="run()"
    >
        <div class="sm:mx-auto sm:w-full sm:max-w-md">
            <x-flux::logo fill="#0690FA" class="h-24" />
        </div>
        <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
            <x-card>
                <div class="space-y-4 py-6 text-center">
                    <template x-if="state === 'starting'">
                        <div>
                            <x-icon
                                name="finger-print"
                                class="mx-auto size-12 text-indigo-600"
                            />
                            <p class="mt-4 text-base font-medium">{{ __('Authenticate with your passkey') }}</p>
                            <p class="mt-2 text-sm text-gray-500">{{ __('Your device should prompt for biometric verification.') }}</p>
                        </div>
                    </template>
                    <template x-if="state === 'submitting'">
                        <div>
                            <x-icon
                                name="arrow-path"
                                class="mx-auto size-12 animate-spin text-indigo-600"
                            />
                            <p class="mt-4 text-base font-medium">{{ __('Signing in…') }}</p>
                        </div>
                    </template>
                    <template x-if="state === 'error'">
                        <div>
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
                                        state = 'starting';
                                        error = null;
                                        run();
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
