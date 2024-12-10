<div x-data="dashboard($wire)">
    <x-flux::dashboard.widget-select />
    <div class="mx-auto md:flex justify-end items-center">
        <x-flux::dashboard.edit-dashboard />
    </div>
    <x-flux::dashboard.grid />
</div>
