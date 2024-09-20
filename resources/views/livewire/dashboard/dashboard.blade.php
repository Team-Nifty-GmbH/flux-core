<div x-data="dashboard($wire)">
    <x-flux::dashboard.widget-select />
    <div class="mx-auto py-6 md:flex justify-between items-center">
        <div class="pb-6 md:flex md:items-center md:justify-between md:space-x-5">
            <div class="flex items-start space-x-5">
                <div class="flex-shrink-0">
                    <x-avatar :src="auth()->user()->getAvatarUrl()" />
                </div>
                <div class="pt-1.5">
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-50">{{ __('Hello') }} {{ Auth::user()->name }}</h1>
                </div>
            </div>
        </div>
        <x-flux::dashboard.edit-dashboard />
    </div>
    <x-flux::dashboard.grid />
</div>
