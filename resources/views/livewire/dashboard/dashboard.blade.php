@extends('flux::livewire.support.dashboard')

@section('dashboard-header')
    <div class="pb-6 md:flex md:items-center md:justify-between md:space-x-5">
        <div class="flex items-start space-x-5">
            @section('dashboard-header.avatar')
                <div class="flex-shrink-0">
                    <x-avatar :image="auth()->user()->getAvatarUrl()" />
                </div>
            @endsection

            @section('dashboard-header.user-name')
                <div class="pt-1.5">
                    <h1
                        class="text-2xl font-bold text-gray-900 dark:text-gray-50"
                    >
                        {{ __('Hello') }} {{ Auth::user()->name }}
                    </h1>
                </div>
            @endsection
        </div>
    </div>
    <x-flux::editor class="w-[200px]" :tooltipDropdown="true" />
@endsection
