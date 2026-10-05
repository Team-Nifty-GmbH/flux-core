<div class="mb-4 flex max-w-xl flex-col gap-4 sm:flex-row">
    <div class="w-full">
        <x-select.styled
            wire:model.live="timeFrame"
            :label="__('Time Frame')"
            required
            :options="[
                ['label' => __('Today'), 'value' => \FluxErp\Enums\TimeFrameEnum::Today],
                ['label' => __('This Week'), 'value' => \FluxErp\Enums\TimeFrameEnum::ThisWeek],
                ['label' => __('This Month'), 'value' => \FluxErp\Enums\TimeFrameEnum::ThisMonth],
                ['label' => __('This Quarter'), 'value' => \FluxErp\Enums\TimeFrameEnum::ThisQuarter],
                ['label' => __('This Year'), 'value' => \FluxErp\Enums\TimeFrameEnum::ThisYear],
                ['label' => __('Days'), 'value' => \FluxErp\Enums\TimeFrameEnum::Custom],
            ]"
        />
    </div>
    <div
        class="w-full"
        x-cloak
        x-show="$wire.timeFrame === '{{ \FluxErp\Enums\TimeFrameEnum::Custom }}'"
    >
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
</div>
