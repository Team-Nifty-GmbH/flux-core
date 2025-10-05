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
    </div>
    <x-flux::tabs wire:model.live="tab" :$tabs />
</div>
