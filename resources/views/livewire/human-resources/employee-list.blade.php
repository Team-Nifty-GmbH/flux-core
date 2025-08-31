<div>
    <div class="mb-4 flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
            {{ __('Employees') }}
        </h1>
        @canAction(\FluxErp\Actions\User\CreateUser::class)
            <x-button
                primary
                wire:click="create"
            >
                <x-icon name="plus" class="w-4 h-4 mr-2" />
                {{ __('New Employee') }}
            </x-button>
        @endcanAction
    </div>

    {{-- Include the data table --}}
    @include('flux::livewire.data-tables.base-data-table')
</div>