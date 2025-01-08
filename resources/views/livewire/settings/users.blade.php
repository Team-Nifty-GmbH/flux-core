<div class="py-6">
    <x-modal max-width="6xl" name="create-user-modal">
        <x-card :title="__('Create User')" footer-classes="flex gap-1.5 justify-end">
            @section('user-edit')
                <form class="space-y-5">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        @section('user-edit.personal-data')
                            <x-input :label="__('Firstname')" wire:model="userForm.firstname"/>
                            <x-input :label="__('Lastname')" wire:model="userForm.lastname"/>
                            <x-input :label="__('Email')" wire:model="userForm.email"/>
                            <x-input :label="__('Phone')" wire:model="userForm.phone"/>
                            <x-input :label="__('User code')" wire:model="userForm.user_code"/>
                            <x-inputs.number
                                :prefix="\FluxErp\Models\Currency::default()?->symbol"
                                :label="__('Cost Per Hour')"
                                wire:model="userForm.cost_per_hour"
                            />
                        @show
                        @section('user-edit.attributes')
                            <x-checkbox :label="__('Active')" wire:model="userForm.is_active" class="col-span-2"/>
                            <div class="col-span-2 flex flex-col gap-4">
                                <x-inputs.password :label="__('New password')" wire:model="userForm.password"/>
                                <x-inputs.password :label="__('Repeat password')" wire:model="userForm.password_confirmation"/>
                            </div>
                        @show
                    </div>
                </form>
            @show
            <x-slot:footer>
                <x-button :label="__('Cancel')" x-on:click="close"/>
                <x-button primary :label="__('Save')" wire:click="save().then((success) => {if(success) close();})"/>
            </x-slot:footer>
        </x-card>
    </x-modal>
</div>
