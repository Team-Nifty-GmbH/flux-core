<div x-data="dashboard()" x-init.once="reInit().disable()">
    @section('dashboard-widget-select')
    <x-flux::dashboard.widget-select />
    @show
    <div class="mx-auto items-center justify-between py-6 md:flex">
        @section('dashboard-header')
        <div
            class="pb-6 md:flex md:items-center md:justify-between md:space-x-5"
        >
            <div class="flex items-start space-x-5">
                @section('dashboard-header.avatar')
                <div class="flex-shrink-0">
                    <x-avatar :image="auth()->user()->getAvatarUrl()" />
                </div>
                @show
                @section('dashboard-header.user-name')
                <div class="pt-1.5">
                    <h1
                        class="text-2xl font-bold text-gray-900 dark:text-gray-50"
                    >
                        {{ __('Hello') }} {{ Auth::user()->name }}
                    </h1>
                </div>
                @show
            </div>
        </div>
        @show
        @section('dashboard-edit')
        <x-flux::dashboard.edit-dashboard />
        @show
    </div>
    <x-flux::dashboard.grid />
</div>
