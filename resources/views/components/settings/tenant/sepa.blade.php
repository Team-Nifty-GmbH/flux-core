@php
    $model = resolve_static(\FluxErp\Models\SepaMandate::class,'query')
        ->where('tenant_id',$this->tenant->id)
        ->first();

    $printView =  $model->getPrintViews()['sepa-mandate'];
    $modelId = $model->id;
    $modelType = get_class($model);

    $this->openPreview($printView, $modelType, $modelId);

@endphp

<div
    x-data="{
        onInit() {

        }
    }"
    x-init="onInit()"
    class="flex flex-col gap-2">
    <x-input
        :label="__('Creditor Identifier')"
        wire:model="tenant.creditor_identifier"
    />
    <x-textarea
        :label="__('Sepa Text Basic')"
        wire:model="tenant.sepa_text_basic"
    />
    <x-textarea
        :label="__('Sepa Text B2B')"
        wire:model="tenant.sepa_text_b2b"
    />
    <div class="flex flex-col w-full gap-4">
        <div>{{ __('Preview') }}</div>
        <iframe
            x-ref="frame"
            loading="lazy"
            src="data:text/html;charset=utf-8,%3Chtml%3E%3Cbody%3E%3C%2Fbody%3E%3C%2Fhtml%3E"
        ></iframe>
        <x-button class="w-32">
            Download
        </x-button>
    </div>
</div>
