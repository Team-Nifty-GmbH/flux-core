<div x-data="{
    init() {
        $watch('{{ $edit }}', (value) => {
            if (!value) {
                $wire.updateBladePreview();
            }
        });
    },
}">
    <x-flux::editor
        wire:model="content"
        :label="$label"
        :blade-variables="$bladeVariables"
        x-model="{{ $edit }}"
    />
    <div
        class="mt-4 p-4 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-md"
    >
        <div class="prose prose-sm dark:prose-invert max-w-none" x-html="$wire.renderedPreview">
        </div>
    </div>
</div>
