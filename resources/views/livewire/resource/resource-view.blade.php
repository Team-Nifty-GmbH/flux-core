<div class="flex flex-col gap-6 dark:text-white" x-data="{}">
    <!-- Page header -->
    <div class="mx-auto w-full px-4 sm:px-6 md:flex md:items-center md:justify-between md:space-x-5 lg:px-8">
        <div class="flex items-center space-x-5">
            <div>
                <x-avatar
                    xl
                    :label="$resource->getFirstMediaUrl('avatar') ? false : strtoupper(substr($resource->name ?? '', 0, 2))"
                    :image="$resource->getFirstMediaUrl('avatar')"
                />
            </div>
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-50">
                    <div
                        class="opacity-40 transition-opacity hover:opacity-100"
                        x-text="$wire.resourceForm.resource_number"
                    ></div>
                    <span x-text="$wire.resourceForm.name"></span>
                </h1>
            </div>
        </div>
        <div class="mt-6 flex flex-col-reverse justify-stretch space-y-4 space-y-reverse sm:flex-row-reverse sm:justify-end sm:space-y-0 sm:space-x-3 sm:space-x-reverse md:mt-0 md:flex-row md:space-x-3">
            @can('action.resource.update')
                <template x-if="$wire.resourceForm.id && $wire.edit === false">
                    <x-button
                        color="indigo"
                        :text="__('Edit')"
                        x-on:click="$wire.startEdit()"
                    />
                </template>
                <template x-if="$wire.edit === true">
                    <div class="flex gap-2">
                        <x-button
                            color="indigo"
                            :text="__('Save')"
                            x-on:click="$wire.save()"
                        />
                        <x-button
                            color="secondary"
                            light
                            :text="__('Cancel')"
                            x-on:click="$wire.cancel()"
                        />
                    </div>
                </template>
            @endcan
        </div>
    </div>

    <!-- Master data card -->
    <x-card :title="__('Master Data')">
        <div class="flex flex-col gap-4">
            <div x-bind:class="!$wire.edit && 'pointer-events-none'">
                <x-input
                    wire:model="resourceForm.name"
                    :label="__('Name')"
                    x-bind:disabled="!$wire.edit"
                />
            </div>

            <div x-bind:class="!$wire.edit && 'pointer-events-none'">
                <x-input
                    wire:model="resourceForm.resource_number"
                    :label="__('Resource Number')"
                    x-bind:disabled="!$wire.edit"
                />
            </div>

            <div x-bind:class="!$wire.edit && 'pointer-events-none'">
                <x-select.styled
                    wire:model="resourceForm.product_id"
                    :label="__('Product')"
                    :placeholder="__('Select')"
                    select="value:id"
                    unfiltered
                    :request="[
                        'url' => route('search', \FluxErp\Models\Product::class),
                        'method' => 'POST',
                    ]"
                />
            </div>

            <x-toggle
                wire:model="resourceForm.allow_overbooking"
                :label="__('Allow Overbooking')"
                x-bind:disabled="!$wire.edit"
            />

            <x-toggle
                wire:model="resourceForm.is_active"
                :label="__('Active')"
                x-bind:disabled="!$wire.edit"
            />

            <div x-bind:class="!$wire.edit && 'pointer-events-none'">
                <x-textarea
                    wire:model="resourceForm.description"
                    :label="__('Description')"
                    x-bind:disabled="!$wire.edit"
                />
            </div>
        </div>
    </x-card>

    <!-- Bookings section -->
    <x-card :title="__('Bookings')">
        <x-slot:action>
            @can('action.resource-booking.create')
                <x-button
                    color="indigo"
                    icon="plus"
                    :text="__('New Booking')"
                    x-on:click="$wire.newBooking()"
                />
            @endcan
        </x-slot:action>

        <livewire:data-tables.resource-booking-list :resource-id="$resource->getKey()" />
    </x-card>

    <!-- Booking form modal -->
    <x-modal :id="$resourceBookingForm->modalName()" size="xl" :title="__('Booking')">
        <div class="flex flex-col gap-4">
            <x-input
                wire:model="resourceBookingForm.resource_id"
                type="hidden"
            />

            <x-input
                wire:model="resourceBookingForm.start"
                type="datetime-local"
                :label="__('Start')"
            />

            <x-input
                wire:model="resourceBookingForm.end"
                type="datetime-local"
                :label="__('End')"
            />

            <x-select.styled
                wire:model="resourceBookingForm.order_id"
                :label="__('Order')"
                :placeholder="__('Select')"
                select="value:id"
                unfiltered
                :request="[
                    'url' => route('search', \FluxErp\Models\Order::class),
                    'method' => 'POST',
                ]"
            />

            <x-textarea
                wire:model="resourceBookingForm.description"
                :label="__('Description')"
            />
        </div>

        <x-slot:footer>
            <x-button
                :text="__('Cancel')"
                color="secondary"
                flat
                x-on:click="$tsui.close.modal('{{ $resourceBookingForm->modalName() }}')"
            />
            <x-button
                :text="__('Save')"
                color="primary"
                x-on:click="$wire.saveBooking().then((success) => { if(success) $tsui.close.modal('{{ $resourceBookingForm->modalName() }}') })"
            />
        </x-slot:footer>
    </x-modal>
</div>
