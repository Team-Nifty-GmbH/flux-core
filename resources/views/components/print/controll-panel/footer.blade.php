<div
    x-show="footerStore.snippetEditorXData != null "
    x-cloak
>
    <div
        class="flex items-center justify-between">
        <x-button
            x-on:click="footerStore.cancelEditor()" text="Cancel" />
        <x-button
            x-on:click="footerStore.saveText()" text="Save Text" />
    </div>
</div>
