<div
    x-show="headerStore.snippetEditorXData != null "
    x-cloak
>
    <div
        class="flex items-center justify-between">
        <x-button
            x-on:click="headerStore.cancelEditor()" :text="__('Cancel')" />
        <x-button
            x-on:click="headerStore.saveText()" :text="__('Save Snippet')" />
    </div>
</div>

