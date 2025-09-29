<div
    x-show="firstPageHeaderStore.snippetEditorXData != null "
    x-cloak
>
    <div
        class="flex items-center justify-between">
        <x-button
            x-on:click="firstPageHeaderStore.cancelEditor()" text="Cancel" />
        <x-button
            x-on:click="firstPageHeaderStore.saveText()" text="Save Text" />
    </div>
</div>
