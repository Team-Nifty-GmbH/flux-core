<div
    x-show="footerStore.snippetEditorXData != null "
    x-cloak
>
    <div
        class="flex items-center justify-between">
        <x-button
            x-on:click="footerStore.cancelEditor()" :text="__('Cancel')" />
        <x-button
            x-on:click="footerStore.saveText()" :text="__('Save Snippet')" />
    </div>
</div>
