<div>
    <x-modal :id="$holidayForm->modalName()" :title="__('Holiday')">
        <div class="flex flex-col gap-4">
            <x-input
                wire:model="holidayForm.name"
                :label="__('Name')"
                required
            />

            <x-toggle
                wire:model.live="holidayForm.is_recurring"
                :label="__('Recurring Holiday')"
                :hint="__('Check for holidays that repeat every year')"
            />

            <div x-show="!$wire.holidayForm.is_recurring" x-cloak>
                <x-date
                    wire:model="holidayForm.date"
                    :label="__('Date')"
                    required
                />
            </div>

            <div
                class="grid grid-cols-2 gap-4"
                x-show="$wire.holidayForm.is_recurring"
                x-cloak
            >
                <x-input
                    wire:model="holidayForm.month"
                    type="number"
                    min="1"
                    max="12"
                    :label="__('Month')"
                    required
                />

                <x-input
                    wire:model="holidayForm.day"
                    type="number"
                    min="1"
                    max="31"
                    :label="__('Day')"
                    required
                />
            </div>

            <x-select.styled
                wire:model="holidayForm.locations"
                :label="__('Location')"
                :hint="__('Leave empty for all locations')"
                multiple
                select="label:name|value:id"
                unfiltered
                :request="[
                    'url' => route('search', \FluxErp\Models\Location::class),
                    'method' => 'POST',
                    'params' => [
                        'searchFields' => ['name']
                    ]
                ]"
            />

            <div class="grid grid-cols-2 gap-4">
                <x-input
                    wire:model="holidayForm.effective_from"
                    type="number"
                    min="2000"
                    max="2100"
                    :label="__('Effective From Year')"
                />

                <x-input
                    wire:model="holidayForm.effective_until"
                    type="number"
                    min="2000"
                    max="2100"
                    :label="__('Effective Until Year')"
                    :hint="__('Leave empty for no end date')"
                />
            </div>

            <x-select.styled
                wire:model="holidayForm.day_part_enum"
                :label="__('Day Part')"
                required
                select="label:label|value:value"
                :options="\FluxErp\Enums\DayPartEnum::valuesLocalized()"
            />

            <x-toggle
                wire:model="holidayForm.is_active"
                :label="__('Is Active')"
            />
        </div>

        <x-slot:footer>
            <x-button
                :text="__('Cancel')"
                color="secondary"
                flat
                x-on:click="$modalClose('{{ $holidayForm->modalName() }}')"
            />
            <x-button
                :text="__('Save')"
                color="primary"
                wire:click="save().then((success) => { if(success) $modalClose('{{ $holidayForm->modalName() }}') })"
            />
        </x-slot>
    </x-modal>
</div>
