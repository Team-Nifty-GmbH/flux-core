<div class="dark:text-white">
    <h2 class="text-base font-bold uppercase">
        {{ __('Welcome') }}
    </h2>
    <h1 class="pt-5 pb-10 text-5xl font-bold">
        {{ __('My orders') }}
    </h1>
    <livewire:portal.data-tables.order-list
        :filters="[
            [
                'contact_id',
                '=',
                auth()->user()->contact_id
            ],
        ]"
    />
</div>
