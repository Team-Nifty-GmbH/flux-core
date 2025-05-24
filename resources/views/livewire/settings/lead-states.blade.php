<x-modal :id="$leadStateForm->modalName()">
    <div class="flex flex-col gap-4">
        <x-input :label="__('Name')" wire:model="leadStateForm.name" />
        <x-color :label="__('Color')" wire:model="leadStateForm.color" />
        <x-range
            wire:model.number="leadStateForm.probability_percentage"
            :hint="__('Probability to win this lead…')"
        >
            <x-slot:label>
                <span
                    x-cloak
                    x-show="$wire.leadStateForm.probability_percentage !== null"
                    x-text="window.formatters.percentage($wire.leadStateForm.probability_percentage / 100)"
                ></span>
            </x-slot>
        </x-range>
        <x-toggle
            :label="__('Default')"
            lg
            x-on:change="if($event.target.checked) $wire.leadStateForm.is_won = false; $wire.leadStateForm.is_lost = false"
            wire:model.boolean="leadStateForm.is_default"
        />
        <x-toggle
            :label="__('Is Won')"
            lg
            x-on:change="if($event.target.checked) $wire.leadStateForm.is_default = false; $wire.leadStateForm.is_lost = false"
            wire:model.boolean="leadStateForm.is_won"
        />
        <x-toggle
            :label="__('Is Lost')"
            lg
            x-on:change="if($event.target.checked) $wire.leadStateForm.is_default = false; $wire.leadStateForm.is_won = false"
            wire:model.boolean="leadStateForm.is_lost"
        />
    </div>
    <x-slot:footer>
        <x-button
            color="secondary"
            light
            flat
            :text="__('Cancel')"
            x-on:click="$modalClose('{{ $leadStateForm->modalName() }}')"
        />
        <x-button
            color="indigo"
            :text="__('Save')"
            wire:click="save().then((success) => { if(success) $modalClose('{{ $leadStateForm->modalName() }}')})"
        />
    </x-slot>
</x-modal>
