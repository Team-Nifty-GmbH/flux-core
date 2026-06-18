<div
    class="mx-auto w-full max-w-2xl p-4"
    data-testid="share-target"
    x-data="{
        state: 'loading',
        async init() {
            const files = await window.nuxbeShareTarget.loadSharedFiles();

            if (files === null) {
                this.state = 'no-bridge';

                return;
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
    }"
    x-on:share-target-completed.window="
        window.nuxbeShareTarget.clearSharedFiles().then(() => {
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
