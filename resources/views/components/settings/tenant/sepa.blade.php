@php
    $model = resolve_static(\FluxErp\Models\SepaMandate::class,'query')
        ->where('tenant_id',$this->tenant->id)
        ->first();

    $printView =  $model->getPrintViews()['sepa-mandate'];
    $modelId = $model->id;
    $modelType = morph_alias($model::class);

    $route = $this->openPreview('sepa-mandate', $modelType, $modelId);

@endphp

<div
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
    <div
        x-data="{
        onInit() {
        this.$nextTick(() => {
            this.$refs.frame.src = '{{ $route }}'
           })
        }
    }"
        x-init="onInit()"
        class="flex flex-col w-full gap-4 h-[800px]">
        <div>{{ __('Preview') }}</div>
        <iframe
            x-ref="frame"
            loading="lazy"
            class="h-full"
        ></iframe>
        <x-button class="w-32">
            Download
        </x-button>
    </div>
</div>
