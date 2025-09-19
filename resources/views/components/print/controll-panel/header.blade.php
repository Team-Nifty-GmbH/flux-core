<div
    x-show="headerStore.snippetEditorXData != null "
    x-cloak
>
    <div
        class="flex items-center justify-between">
        <x-button
            x-on:click="headerStore.cancelEditor()" text="Cancel" />
        <x-button
            x-on:click="headerStore.saveText()" text="Save Text" />
    </div>
</div>

