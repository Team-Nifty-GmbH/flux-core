<div
    x-data="{
        userId: @entangle('userId'),
        contactId: @entangle('contactId'),
        create: @entangle('create')
    }"
    x-on:data-table-row-clicked="$wire.show($event.detail.id)"
>
    <x-modal id="edit-commission-rate" z-index="z-30" wire="showModal" :title="$create ? __('Create Commission Rate') : __('Edit Commission Rate')">
        <div class="space-y-8 divide-y divide-gray-200">
            <div class="space-y-8 divide-y divide-gray-200">
                <div>
                    <div class="mt-6 grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                        <div class="sm:col-span-6" x-show="!userId">
                            <x-select.styled
                                :label="__('Commission Agent')"
                                wire:model="commissionRate.user_id"
                                select="label:label|value:id"
                                :disabled="! $create"
                                required
                                :template="[
                                    'name'   => 'user-option',
                                ]"
                                :request="[
                                    'url' => route('search', \FluxErp\Models\User::class),
                                    'method' => 'POST',
                                    'params' => [
                                        'with' => 'media',
                                    ],
                                ]"
                            />
                        </div>
                        <div class="sm:col-span-6">
                            <x-select.styled
                                :label="__('Category')"
                                wire:model.live="commissionRate.category_id"
                                :options="$categories"
                                select="label:name|value:id"
                            />
                        </div>
                        <div class="sm:col-span-6">
                            <x-select.styled
                                :label="__('Product')"
                                wire:model.live="commissionRate.product_id"
                                select="label:label|value:id"
                                option-description="product_number"
                                :template="[
                                    'name'   => 'user-option',
                                ]"
                                :request="[
                                    'url' => route('search', \FluxErp\Models\Product::class),
                                    'params' => [
                                        'fields' => [
                                            'id',
                                            'name',
                                            'product_number',
                                        ],
                                        'with' => 'media',
                                    ],
                                ]"
                            />
                        </div>
                        <div class="sm:col-span-6">
                            <x-number label="{{ __('Commission Rate (in %)') }}"
                                 placeholder="{{ __('Commission Rate') }}"
                                 wire:model="commissionRate.commission_rate"
                                 step="0.01"
                                 min="0.01"
                                 max="99.99"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <x-slot name="footer">
            <div class="w-full">
                <div
                    class="flex justify-between gap-x-4">
                    @if(user_can('action.commission-rates.delete'))
                        <x-button x-bind:class="! create || 'invisible'" flat color="red" :text="__('Delete') "
                                  x-on:click="window.$wireui.confirmDialog({
                                      title: '{{ __('Delete commission rate') }}',
                                      description: '{{ __('Do you really want to delete this commission rate?') }}',
                                      icon: 'error',
                                      accept: {
                                          label: '{{ __('Delete') }}',
                                          execute: () => {
                                              $wire.delete()
                                          }
                                      },
                                      reject: {
                                          label: '{{ __('Cancel') }}',
                                      }
                                  }, $wire.__instance.id)"
                                  label="{{ __('Delete') }}"
                        />
                    @endif
                    <div class="flex">
                        <x-button color="secondary" light flat :text="__('Cancel')" x-on:click="$modalClose('edit-commission-rate')"/>
                        <x-button color="indigo" :text="__('Save')" wire:click="save"/>
                    </div>
                </div>
            </div>
        </x-slot>
    </x-modal>

    @include('tall-datatables::livewire.data-table')
</div>
