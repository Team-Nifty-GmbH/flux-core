<div class="mb-4 max-w-xs">
    <x-select.styled
        wire:model.live.number="days"
        :label="__('Days')"
        select="label:label|value:value"
        :options="[
            ['label' => '7', 'value' => 7],
            ['label' => '30', 'value' => 30],
            ['label' => '90', 'value' => 90],
            ['label' => '180', 'value' => 180],
            ['label' => '365', 'value' => 365],
        ]"
    />
</div>
