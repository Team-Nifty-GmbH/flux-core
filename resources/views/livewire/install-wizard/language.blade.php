<x-input
    autofocus
    wire:model="languageForm.name"
    placeholder="e.g. English…"
    :label="__('Name')"
/>
<x-input
    wire:model="languageForm.language_code"
    placeholder="e.g. en…"
    :label="__('Language Code')"
/>
