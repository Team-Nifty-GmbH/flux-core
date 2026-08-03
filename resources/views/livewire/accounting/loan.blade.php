<div
    x-data="{
        isEditing: false,
    }"
>
    <div
        class="mx-auto md:flex md:items-center md:justify-between md:space-x-5"
    >
        <div class="flex items-center space-x-5">
            @section('loan.title')
                <div>
                    <h1
                        class="text-2xl font-bold text-gray-900 dark:text-gray-50"
                    >
                        <span x-text="$wire.loan.name"></span>
                    </h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        <span x-text="$wire.loan.number"></span>
                    </p>
                </div>
            @show
        </div>
        <div
            class="mt-6 flex flex-col-reverse justify-stretch space-y-4 space-y-reverse sm:flex-row-reverse sm:justify-end sm:space-y-0 sm:space-x-3 sm:space-x-reverse md:mt-0 md:flex-row md:space-x-3"
        >
            <x-button
                color="indigo"
                x-cloak
                x-show="!isEditing"
                class="w-full"
                x-on:click="isEditing = true"
                :text="__('Edit')"
            />
            <x-button
                color="indigo"
                loading="save"
                x-cloak
                x-show="isEditing"
                class="w-full"
                x-on:click="
                    $wire.save().then((success) => {
                        if (success) isEditing = false;
                    })
                "
                :text="__('Save')"
            />
            <x-button
                color="secondary"
                light
                flat
                :text="__('Cancel')"
                x-cloak
                x-show="isEditing"
                class="w-full"
                x-on:click="
                    isEditing = false;
                    $wire.resetForm();
                "
            />
            @canAction(\FluxErp\Actions\Loan\DeleteLoan::class)
                <x-button
                    color="red"
                    :text="__('Delete')"
                    x-cloak
                    x-show="!isEditing"
                    class="w-full"
                    wire:click="delete()"
                    wire:flux-confirm.type.error="{{ __('wire:confirm.delete', ['model' => __('Loan')]) }}"
                />
            @endcanAction
            @stack('loan-detail-header-actions')
        </div>
    </div>
    <x-flux::tabs wire:model.live="tab" :$tabs />
    @stack('loan-detail-after-tabs')
</div>
