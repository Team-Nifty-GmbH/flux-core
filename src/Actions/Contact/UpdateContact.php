<?php

namespace FluxErp\Actions\Contact;

use FluxErp\Actions\Discount\CreateDiscount;
use FluxErp\Actions\DispatchableFluxAction;
use FluxErp\Contracts\SupportsBulkExecution;
use FluxErp\Models\Contact;
use FluxErp\Models\LedgerAccount;
use FluxErp\Models\PaymentType;
use FluxErp\Models\Pivots\ContactTenant;
use FluxErp\Models\Scopes\UserTenantScope;
use FluxErp\Models\Tenant;
use FluxErp\Rulesets\Contact\UpdateContactRuleset;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

class UpdateContact extends DispatchableFluxAction implements SupportsBulkExecution
{
    public static function models(): array
    {
        return [Contact::class];
    }

    protected function getRulesets(): string|array
    {
        return UpdateContactRuleset::class;
    }

    public function performAction(): Model
    {
        $discountGroups = Arr::pull($this->data, 'discount_groups');
        $discounts = Arr::pull($this->data, 'discounts');
        $industries = Arr::pull($this->data, 'industries');
        $tenants = Arr::pull($this->data, 'tenants');
        $expenseLedgerAccounts = Arr::pull($this->data, 'expense_ledger_accounts');

        $contact = resolve_static(Contact::class, 'query')
            ->whereKey($this->data['id'])
            ->first();

        $contact->fill($this->data);
        $contact->save();

        if (! is_null($tenants)) {
            $contact->tenants()->sync($tenants);
        }

        foreach ($expenseLedgerAccounts ?? [] as $entry) {
            $contact->tenants()->updateExistingPivot(
                data_get($entry, 'tenant_id'),
                ['expense_ledger_account_id' => data_get($entry, 'expense_ledger_account_id')]
            );
        }

        if (! is_null($discounts)) {
            $syncType = match ($this->getData('discounts_pivot_sync_type')) {
                'attach' => 'attach',
                'detach' => 'detach',
                'syncWithoutDetaching' => 'syncWithoutDetaching',
                default => 'sync',
            };

            $selectedDiscounts = [];

            foreach ($discounts as $discount) {
                if ($discountId = data_get($discount, 'id')) {
                    $selectedDiscounts[] = $discountId;

                    continue;
                }

                if ($syncType !== 'detach') {
                    $selectedDiscounts[] = CreateDiscount::make([
                        ...$discount,
                        'model_type' => $contact->getMorphClass(),
                        'model_id' => $contact->getKey(),
                    ])
                        ->checkPermission()
                        ->validate()
                        ->execute()
                        ->getKey();
                }
            }

            $contact->discounts()->{$syncType}($selectedDiscounts);
        }

        if (! is_null($discountGroups)) {
            $contact->discountGroups()->sync($discountGroups);
        }

        if (! is_null($industries)) {
            $contact->industries()->sync($industries);
        }

        return $contact->withoutRelations()->fresh();
    }

    protected function prepareForValidation(): void
    {
        $contact = resolve_static(Contact::class, 'query')
            ->whereKey($this->getData('id'))
            ->first();

        $this->data['payment_type_id'] ??= $contact?->payment_type_id;
    }

    protected function validateData(): void
    {
        parent::validateData();

        $errors = [];

        if ($customerNumber = $this->getData('customer_number')) {
            $currentCustomerNumber = resolve_static(Contact::class, 'query')
                ->whereKey($this->getData('id'))
                ->value('customer_number');

            if ($customerNumber !== $currentCustomerNumber) {
                $customerNumberExists = resolve_static(Contact::class, 'query')
                    ->whereKeyNot($this->getData('id'))
                    ->where('customer_number', $customerNumber)
                    ->exists();

                if ($customerNumberExists) {
                    $errors += [
                        'customer_number' => ['Customer number already exists'],
                    ];
                }
            }
        }

        $paymentTypeId = array_key_exists('payment_type_id', $this->data)
            ? $this->getData('payment_type_id')
            : resolve_static(Contact::class, 'query')
                ->whereKey($this->getData('id'))
                ->value('payment_type_id');
        if ($paymentTypeId || $this->getData('tenants')) {
            $tenants = $this->getData('tenants') ?? resolve_static(ContactTenant::class, 'query')
                ->where('contact_id', $this->getData('id'))
                ->pluck('tenant_id')
                ->toArray();

            if (resolve_static(PaymentType::class, 'query')
                ->whereKey($paymentTypeId)
                ->whereHasTenant(
                    $tenants ?: resolve_static(Tenant::class, 'query')->pluck('id')->toArray()
                )
                ->doesntExist()
            ) {
                $errors += [
                    'payment_type_id' => [
                        __(
                            'Payment type with id: \':paymentTypeId\' doesnt match with the associated tenants',
                            [
                                'paymentTypeId' => $this->getData('payment_type_id'),
                            ]
                        ),
                    ],
                ];
            }
        }

        if ($expenseLedgerAccountId = $this->getData('expense_ledger_account_id')) {
            $storedTenants = resolve_static(ContactTenant::class, 'query')
                ->where('contact_id', $this->getData('id'))
                ->pluck('tenant_id')
                ->toArray();
            $tenants = $this->getData('tenants') ?? $storedTenants;
            $isUnchanged = (int) $expenseLedgerAccountId === resolve_static(Contact::class, 'query')
                ->whereKey($this->getData('id'))
                ->value('expense_ledger_account_id')
                && ! array_diff($tenants, $storedTenants)
                && ! array_diff($storedTenants, $tenants);

            if ($tenants
                && ! $isUnchanged
                && resolve_static(LedgerAccount::class, 'query')
                    ->whereKey($expenseLedgerAccountId)
                    ->whereIntegerInRaw('tenant_id', $tenants)
                    ->doesntExist()
            ) {
                $errors += [
                    'expense_ledger_account_id' => ['The expense ledger account must belong to a tenant of the contact.'],
                ];
            }
        }

        if ($this->getData('expense_ledger_accounts')) {
            $tenants = $this->getData('tenants') ?? resolve_static(ContactTenant::class, 'query')
                ->where('contact_id', $this->getData('id'))
                ->pluck('tenant_id')
                ->toArray();

            foreach ($this->getData('expense_ledger_accounts') ?? [] as $index => $entry) {
                $tenantId = data_get($entry, 'tenant_id');

                if (! in_array($tenantId, $tenants)) {
                    $errors['expense_ledger_accounts.' . $index . '.tenant_id'] = [
                        'The tenant is not assigned to the contact.',
                    ];

                    continue;
                }

                if (($ledgerAccountId = data_get($entry, 'expense_ledger_account_id'))
                    && resolve_static(LedgerAccount::class, 'query')
                        ->withoutGlobalScope(UserTenantScope::class)
                        ->whereKey($ledgerAccountId)
                        ->where('tenant_id', $tenantId)
                        ->doesntExist()
                ) {
                    $errors['expense_ledger_accounts.' . $index . '.expense_ledger_account_id'] = [
                        'The expense ledger account must belong to this tenant.',
                    ];
                }
            }
        }

        if ($errors) {
            throw ValidationException::withMessages($errors)->errorBag('updateContact');
        }
    }
}
