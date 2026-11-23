<?php

namespace FluxErp\Support\Matching;

use FluxErp\Actions\LoanInstallmentTransaction\CreateLoanInstallmentTransaction;
use FluxErp\Models\LoanInstallment;
use FluxErp\Models\Transaction;
use FluxErp\Settings\AccountingSettings;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Exceptions\UnauthorizedException;

class LoanInstallmentMatcher
{
    protected const DUE_DATE_WINDOW_IN_DAYS = 10;

    public function match(Transaction $transaction): bool
    {
        $installment = $this->findInstallment($transaction);

        if (is_null($installment)) {
            return false;
        }

        try {
            CreateLoanInstallmentTransaction::make([
                'loan_installment_id' => $installment->getKey(),
                'transaction_id' => $transaction->getKey(),
                'amount' => $transaction->balance,
                'is_accepted' => $this->shouldAutoAccept($transaction, $installment),
            ])
                ->validate()
                ->execute();
        } catch (ValidationException|UnauthorizedException) {
            return false;
        }

        return true;
    }

    protected function findInstallment(Transaction $transaction): ?LoanInstallment
    {
        $iban = $this->normalizeIban($transaction);
        $purpose = strtolower($transaction->purpose ?? '');

        if (blank($iban) && blank($purpose)) {
            return null;
        }

        $installment = resolve_static(LoanInstallment::class, 'query')
            ->when(
                $this->isChargeback($transaction),
                fn (Builder $query) => $query->covered(),
                fn (Builder $query) => $query->unsettled()
            )
            ->whereHas(
                'loan',
                fn (Builder $query) => $query->where(function (Builder $query) use ($iban, $purpose): void {
                    $query->when(
                        filled($iban),
                        fn (Builder $query) => $query->whereHas(
                            'contact.contactBankConnections',
                            fn (Builder $query) => $query->whereRaw(
                                "REPLACE(UPPER(iban), ' ', '') = ?",
                                [$iban]
                            )
                        )
                    )
                        ->when(
                            filled($purpose),
                            fn (Builder $query) => $query->orWhereRaw(
                                'LOWER(number) <> \'\' AND LOCATE(LOWER(number), ?) > 0',
                                [$purpose]
                            )
                        );
                })
            )
            ->orderByRaw(
                'ABS(ROUND(principal_amount + interest_amount, 2) - ROUND(?, 2))',
                [bcabs((string) $transaction->balance)]
            )
            ->orderByRaw('ABS(DATEDIFF(due_date, ?))', [$transaction->booking_date?->format('Y-m-d')])
            ->first();

        if (is_null($installment) || $this->isPlausible($transaction, $installment, $purpose)) {
            return $installment;
        }

        return null;
    }

    protected function isChargeback(Transaction $transaction): bool
    {
        return bccomp((string) $transaction->balance, '0', 2) === 1;
    }

    protected function isPlausible(Transaction $transaction, LoanInstallment $installment, string $purpose): bool
    {
        $number = strtolower((string) $installment->loan?->number);

        if (filled($number) && str_contains($purpose, $number)) {
            return true;
        }

        return bccomp(bcabs((string) $transaction->balance), $installment->getTotalAmount(), 2) === 0;
    }

    protected function normalizeIban(Transaction $transaction): string
    {
        return str_replace(' ', '', strtoupper($transaction->counterpart_iban ?? ''));
    }

    protected function shouldAutoAccept(Transaction $transaction, LoanInstallment $installment): bool
    {
        if (! app(AccountingSettings::class)->auto_accept_secure_transaction_matches) {
            return false;
        }

        $iban = $this->normalizeIban($transaction);

        if (blank($iban)
            || $installment->loan
                ?->contact
                ?->contactBankConnections()
                ->whereRaw("REPLACE(UPPER(iban), ' ', '') = ?", [$iban])
                ->doesntExist()
        ) {
            return false;
        }

        if (bccomp(bcabs((string) $transaction->balance), $installment->getTotalAmount(), 2) !== 0) {
            return false;
        }

        return ! is_null($transaction->booking_date)
            && $transaction->booking_date->diffInDays($installment->due_date, absolute: true)
                <= static::DUE_DATE_WINDOW_IN_DAYS;
    }
}
