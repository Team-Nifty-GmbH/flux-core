<div
    x-show="firstPageHeaderStore.snippetEditorXData != null "
    x-cloak
>
    <div
        class="flex items-center justify-between">
        <x-button
            x-on:click="firstPageHeaderStore.cancelEditor()" text="{{ __('Cancel') }}" />
        <x-button
            x-on:click="firstPageHeaderStore.saveText()" text="{{ __("Save Snippet") }}" />
    </div>
</div>
