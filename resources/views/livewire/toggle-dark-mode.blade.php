<div class="flex items-center justify-center gap-x-2" x-data="{
        dark: $wire.entangle('dark', true),
        browserDarkMode() {
            return window.matchMedia('(prefers-color-scheme: dark)').matches
        },
        enable() {
            this.dark = true
            window.localStorage.setItem('dark', true)
            document.documentElement.classList.add('dark')
        },
        disable() {
            this.dark = false
            window.localStorage.setItem('dark', false)
            document.documentElement.classList.remove('dark')
        },
        syncDarkMode() {
            this.dark ? this.enable() : this.disable()
        },
        init() {
            this.syncDarkMode();
            $watch('dark', () => this.syncDarkMode());
        }
    }"
>
    <x-icon
        class="dark:text-secondary-200 h-5 w-5 cursor-pointer text-gray-700"
        x-on:click="disable"
        name="sun"
    />
    <x-toggle x-model="dark" id="dark-mode-toggle.{{ $this->getId() }}" />
    <x-icon
        class="dark:text-secondary-200 h-5 w-5 cursor-pointer text-gray-700"
        x-on:click="enable"
        name="moon"
    />
</div>
