<div x-data="{
    edit: false,
}">
    <div
        class="mx-auto md:flex md:items-center md:justify-between md:space-x-5"
    >
        <div class="flex items-center space-x-5">
            @section('employee.title')
            @section('employee.title.avatar')
            <label for="avatar" class="cursor-pointer">
                <x-avatar xl :image="$avatar" />
            </label>
            <input
                type="file"
                accept="image/*"
                id="avatar"
                class="hidden"
                wire:model.live="avatar"
            />
            @show
            @section('employee.title.name')
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-50">
                    <div class="flex">
                        <div class="pl-2">
                            <span>{{ $employee->firstname }} {{ $employee->lastname }}</span>
                        </div>
                    </div>
                </h1>
                <div class="flex gap-4 text-sm text-gray-500 dark:text-gray-400">
                    <span x-show="$wire.employee.email">{{ $employee->email }}</span>
                    <span x-show="$wire.employee.employee_number">#{{ $employee->employee_number }}</span>
                    <span x-show="$wire.employee.job_title">{{ $employee->job_title }}</span>
                </div>
            </div>
            @show
            @show
        </div>
        <div
            class="mt-6 flex flex-col-reverse justify-stretch space-y-4 space-y-reverse sm:flex-row-reverse sm:justify-end sm:space-x-3 sm:space-y-0 sm:space-x-reverse md:mt-0 md:flex-row md:space-x-3"
        >
            <x-button
                color="indigo"
                x-cloak
                x-show="!edit"
                class="w-full"
                x-on:click="edit = true"
                :text="__('Edit')"
            />
            <x-button
                x-cloak
                color="indigo"
                loading="save"
                x-cloak
                x-show="edit"
                class="w-full"
                x-on:click="$wire.save().then((success) => {
                    edit = false;
                });"
                :text="__('Save')"
            />
            <x-button
                color="secondary"
                light
                flat
                :text="__('Cancel')"
                x-cloak
                loading="save"
                x-show="edit"
                class="w-full"
                x-on:click="edit = false; $wire.resetForm();"
            />
        </div>
    </div>
    <x-flux::tabs wire:model.live="tab" :$tabs />
</div>
