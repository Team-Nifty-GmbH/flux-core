<x-modal id="edit-loan-modal" :title="__('Loan')" size="xl">
    <div class="flex flex-col gap-1.5">
        <x-input wire:model="loan.name" :label="__('Name')" required />
        <div class="grid grid-cols-1 gap-1.5 md:grid-cols-2">
            <x-select.styled
                :label="__('Contact')"
                wire:model.number="loan.contact_id"
                select="label:label|value:id"
                required
                :request="[
                    'url' => route('search', \FluxErp\Models\Contact::class),
                    'method' => 'POST',
                ]"
            />
            <x-select.styled
                :label="__('Ledger Account')"
                wire:model.number="loan.ledger_account_id"
                select="label:name|value:id|description:number"
                required
                unfiltered
                :request="[
                    'url' => route('search', \FluxErp\Models\LedgerAccount::class),
                    'method' => 'POST',
                ]"
            />
        </div>
        <x-select.styled
            :label="__('Order')"
            wire:model.number="loan.order_id"
            select="label:label|value:id"
            :request="[
                'url' => route('search', \FluxErp\Models\Order::class),
                'method' => 'POST',
            ]"
        />
        <div class="grid grid-cols-1 gap-1.5 md:grid-cols-2">
            <x-number
                wire:model="loan.amount"
                :label="__('Amount')"
                step="0.01"
                placeholder="0.00"
            />
            <x-number
                wire:model="loan.interest_rate"
                :label="__('Interest Rate')"
                step="0.0001"
                placeholder="0.0000"
            />
        </div>
        <div class="grid grid-cols-1 gap-1.5 md:grid-cols-3">
            <x-select.native
                wire:model="loan.repayment_type_enum"
                :label="__('Repayment Type')"
                required
            >
                @foreach (\FluxErp\Enums\RepaymentTypeEnum::cases() as $case)
                    <option value="{{ $case->value }}">
                        {{ __(\Illuminate\Support\Str::headline($case->value)) }}
                    </option>
                @endforeach
            </x-select.native>
            <x-number
                wire:model="loan.number_of_installments"
                :label="__('Number Of Installments')"
                step="1"
            />
            <x-date wire:model="loan.starts_at" :label="__('Starts At')" />
        </div>
        <x-input wire:model="loan.note" :label="__('Note')" />

        @if ($installments)
            <div class="mt-4">
                <x-table>
                    <x-slot:header>
                        <table.row>
                            <th class="text-left">{{ __('Sequence') }}</th>
                            <th class="text-left">{{ __('Due Date') }}</th>
                            <th class="text-right">{{ __('Principal') }}</th>
                            <th class="text-right">{{ __('Interest') }}</th>
                            <th class="text-right">{{ __('Remaining') }}</th>
                            <th class="text-left">{{ __('Paid') }}</th>
                        </table.row>
                    </x-slot:header>
                    @foreach ($installments as $installment)
                        <x-table.row>
                            <td>{{ $installment['sequence'] }}</td>
                            <td>{{ $installment['due_date'] }}</td>
                            <td class="text-right">
                                {{ number_format((float) $installment['principal_amount'], 2) }}
                            </td>
                            <td class="text-right">
                                {{ number_format((float) $installment['interest_amount'], 2) }}
                            </td>
                            <td class="text-right">
                                {{ number_format((float) $installment['remaining'], 2) }}
                            </td>
                            <td>
                                <x-icon
                                    :name="$installment['is_paid'] ? 'check' : 'x-mark'"
                                    class="h-5 w-5"
                                />
                            </td>
                        </x-table.row>
                    @endforeach
                </x-table>
            </div>
        @endif
    </div>
    <x-slot:footer>
        <x-button
            color="secondary"
            light
            flat
            :text="__('Cancel')"
            x-on:click="$tsui.close.modal('edit-loan-modal')"
        />
        <x-button
            color="indigo"
            :text="__('Save')"
            x-on:click="
                $wire.save().then((success) => {
                    if (success) $tsui.close.modal('edit-loan-modal');
                })
            "
        />
    </x-slot:footer>
</x-modal>
