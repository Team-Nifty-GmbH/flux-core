<div
    class="mx-auto w-full max-w-2xl p-4"
    data-testid="share-target"
    x-data="{
        state: 'loading',
        async init() {
            const plugins = window.Capacitor?.Plugins;

            if (!plugins?.Preferences || !plugins?.Filesystem) {
                this.state = 'no-bridge';

                return;
            }

            const { value } = await plugins.Preferences.get({
                key: 'pending_shared_files',
            });
            const metas = value ? JSON.parse(value) : [];

            if (!metas.length) {
                this.state = 'empty';

                return;
            }

            const files = [];

            for (const meta of metas) {
                try {
                    const { data } = await plugins.Filesystem.readFile({
                        path: meta.path,
                        directory: 'CACHE',
                    });
                    const bytes = Uint8Array.from(atob(data), (char) =>
                        char.charCodeAt(0),
                    );

                    files.push(
                        new File([bytes], meta.name, {
                            type: meta.mimeType || 'application/octet-stream',
                        }),
                    );
                } catch (error) {
                    console.error(
                        '[SHARE TARGET] Failed to read shared file:',
                        meta.path,
                        error,
                    );
                }
            }

            if (!files.length) {
                this.state = 'empty';

                return;
            }

            this.state = 'uploading';

            this.$wire.uploadMultiple(
                'files',
                files,
                () => {
                    this.state = 'ready';
                },
                () => {
                    this.state = 'error';
                },
            );
        },
        async cleanup() {
            const plugins = window.Capacitor?.Plugins;

            if (!plugins?.Preferences || !plugins?.Filesystem) {
                return;
            }

            try {
                await plugins.Preferences.remove({
                    key: 'pending_shared_files',
                });
                await plugins.Filesystem.rmdir({
                    path: 'shared_files',
                    directory: 'CACHE',
                    recursive: true,
                });
            } catch (error) {
                console.error('[SHARE TARGET] Cleanup failed:', error);
            }
        },
    }"
    x-on:share-target-completed.window="
        cleanup().then(() => {
            window.location.href = $event.detail.redirect;
        })
    "
>
    <x-card>
        <x-slot:header>
            <div class="px-4 py-3 text-lg font-semibold">
                {{ __('Shared files') }}
            </div>
        </x-slot:header>

        <div
            x-show="state === 'loading' || state === 'uploading'"
            x-cloak
            class="flex flex-col items-center gap-3 py-8"
        >
            <x-icon
                name="arrow-path"
                class="h-6 w-6 animate-spin text-gray-400"
            />
            <span class="text-sm text-gray-500 dark:text-gray-400">
                {{ __('Loading shared files…') }}
            </span>
        </div>

        <div
            x-show="state === 'no-bridge'"
            x-cloak
            class="py-8 text-center text-sm text-gray-500 dark:text-gray-400"
        >
            {{ __('This page is only available inside the Nuxbe mobile app.') }}
        </div>

        <div
            x-show="state === 'empty'"
            x-cloak
            class="py-8 text-center text-sm text-gray-500 dark:text-gray-400"
        >
            {{ __('No shared files found. Share a file with the Nuxbe app to get started.') }}
        </div>

        <div
            x-show="state === 'error'"
            x-cloak
            class="py-8 text-center text-sm text-red-500"
        >
            {{ __('The shared files could not be uploaded. Please try again.') }}
        </div>

        <div x-show="state === 'ready'" x-cloak class="flex flex-col gap-4">
            <div class="flex flex-col gap-2">
                @foreach ($this->files as $file)
                    <div
                        class="flex items-center gap-3 rounded-lg border border-gray-200 p-3 dark:border-gray-700"
                    >
                        <x-icon
                            name="document"
                            class="h-6 w-6 shrink-0 text-gray-400"
                        />
                        <div class="min-w-0 flex-1">
                            <div class="truncate text-sm font-medium">
                                {{ $file->getClientOriginalName() }}
                            </div>
                            <div
                                class="text-xs text-gray-500 dark:text-gray-400"
                            >
                                {{ \Illuminate\Support\Number::fileSize($file->getSize()) }}
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="flex flex-col gap-2">
                <div
                    class="text-sm font-medium text-gray-700 dark:text-gray-300"
                >
                    {{ __('Choose an action') }}
                </div>

                @foreach ($this->actions as $action)
                    <x-button
                        :text="$action['label']"
                        :icon="$action['icon']"
                        :disabled="! $action['enabled']"
                        color="primary"
                        wire:click="executeAction(@js($action['class']))"
                        wire:loading.attr="disabled"
                    />
                @endforeach
            </div>
        </div>
    </x-card>
</div>
