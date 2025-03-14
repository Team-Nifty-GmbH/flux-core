<div
    class="absolute w-full p-16"
    x-data="{
        progress: {
            started: false,
            progress: 0,
            tile: null,
            message: [],
        },
        resetProgress() {
            this.progress = {
                started: true,
                progress: 0,
                title: 'Starting...',
                message: [],
            }
        },
        updateProgress(e) {
            if (e.title !== null) this.progress.title = e.title
            if (e.progress !== null) this.progress.progress = e.progress
            if (e.message !== null) {
                this.progress.message = [...this.progress.message, ...e.message]
                $nextTick(() => {
                    this.$refs.progressMessage.scrollIntoView({
                        behavior: 'smooth',
                    })
                })
            }
        },
    }"
    x-on:batch-id="
        window.Echo.channel('job-batch.' + $event.detail).listen(
            '.FluxErp\\Events\\InstallProcessOutputEvent',
            (e) => {
                updateProgress(e)
            },
        )
    "
>
    <x-flux::logo fill="#000000" class="h-24" />
    <x-card :header="$this->title">
        <div class="flex flex-col gap-4">
            @include('flux::livewire.install-wizard.' . $this->steps[$step]['view'])
        </div>
        <x-slot:footer>
            <div
                class="flex justify-between"
                x-cloak
                x-show="progress.progress === 100"
            >
                <div>
                    @if ($step > 0)
                        <x-button
                            color="secondary"
                            light
                            flat
                            :text="__('Back')"
                            x-on:click="$wire.step--; $wire.$refresh();"
                        />
                    @endif
                </div>
                <x-button loading color="indigo" wire:click="continue">
                    {{ __('Continue') }}
                </x-button>
            </div>
        </x-slot>
    </x-card>
</div>
