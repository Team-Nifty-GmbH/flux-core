<div class="py-6">
    <x-modal size="6xl" id="create-user-modal" :title="__('Create User')">
        @section("user-edit")
        <form class="space-y-5">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                @section("user-edit.personal-data")
                <x-input
                    :label="__('Firstname')"
                    wire:model="userForm.firstname"
                />
                <x-input
                    :label="__('Lastname')"
                    wire:model="userForm.lastname"
                />
                <x-input :label="__('Email')" wire:model="userForm.email" />
                <x-input :label="__('Phone')" wire:model="userForm.phone" />
                <x-input
                    :label="__('User code')"
                    wire:model="userForm.user_code"
                />
                <x-color :label="__('Color')" wire:model="userForm.color" />
                <x-number
                    :prefix="\FluxErp\Models\Currency::default()?->symbol"
                    :label="__('Cost Per Hour')"
                    wire:model="userForm.cost_per_hour"
                />
                @show
            </div>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                @section("user-edit.employment")
                <x-date
                    :without-time="true"
                    :label="__('Date Of Birth')"
                    wire:model="userForm.date_of_birth"
                />
                <x-input
                    :label="__('Employee Number')"
                    wire:model="userForm.employee_number"
                />
                <x-date
                    :without-time="true"
                    :label="__('Employment Date')"
                    wire:model="userForm.employment_date"
                />
                <x-date
                    :without-time="true"
                    :label="__('Termination Date')"
                    wire:model="userForm.termination_date"
                />
                @show
            </div>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                @section("user-edit.attributes")
                <x-checkbox
                    :label="__('Active')"
                    wire:model="userForm.is_active"
                    class="col-span-2"
                />
                <div class="col-span-2 flex flex-col gap-4">
                    <x-password
                        :label="__('New password')"
                        wire:model="userForm.password"
                    />
                    <x-password
                        :label="__('Repeat password')"
                        wire:model="userForm.password_confirmation"
                    />
                </div>
                @show
            </div>
        </form>
        @show
        <x-slot:footer>
            <x-button
                color="secondary"
                light
                :text="__('Cancel')"
                x-on:click="$modalClose('create-user-modal')"
            />
            <x-button
                color="indigo"
                :text="__('Save')"
                wire:click="save().then((success) => {if(success) $modalClose('create-user-modal');})"
            />
        </x-slot>
    </x-modal>
</div>
