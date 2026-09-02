<?php

namespace FluxErp\Models\Pivots;

use FluxErp\Actions\LedgerBooking\CreateLedgerBooking;
use FluxErp\Actions\LedgerBooking\DeleteLedgerBooking;
use FluxErp\Actions\LedgerBooking\UpdateLedgerBooking;
use FluxErp\Models\LedgerBooking;
use FluxErp\Models\Loan;
use FluxErp\Models\LoanInstallment;
use FluxErp\Models\Transaction;
use FluxErp\Traits\Model\HasPackageFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class LoanInstallmentTransaction extends FluxPivot
{
    use HasPackageFactory;

    public $timestamps = true;

    protected $table = 'loan_installment_transaction';

    protected static function booted(): void
    {
        static::saved(function (LoanInstallmentTransaction $loanInstallmentTransaction): void {
            $loanInstallmentTransaction->recalculate();
            $loanInstallmentTransaction->syncLedgerBooking();
        });

        static::deleted(function (LoanInstallmentTransaction $loanInstallmentTransaction): void {
            $loanInstallmentTransaction->recalculate();
            $loanInstallmentTransaction->deleteLedgerBookings();
        });
    }

    protected function casts(): array
    {
        return [
            'is_accepted' => 'boolean',
        ];
    }

    /**
     * @return MorphMany<LedgerBooking, $this>
     */
    public function ledgerBookings(): MorphMany
    {
        return $this->morphMany(LedgerBooking::class, 'source');
    }

    /**
     * @return BelongsTo<LoanInstallment, $this>
     */
    public function loanInstallment(): BelongsTo
    {
        return $this->belongsTo(LoanInstallment::class);
    }

    /**
     * @return BelongsTo<Transaction, $this>
     */
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function recalculate(): void
    {
        $previousInstallmentId = $this->getOriginal('loan_installment_id');

        if (! is_null($previousInstallmentId)
            && $previousInstallmentId !== $this->loan_installment_id
        ) {
            resolve_static(LoanInstallment::class, 'query')
                ->whereKey($previousInstallmentId)
                ->first()
                ?->recalculateLoan();
        }

        $this->loanInstallment()
            ->first()
            ?->recalculateLoan();

        if ($this->is_accepted
            || $this->wasChanged('is_accepted')
            || ! $this->exists
        ) {
            $this->transaction()
                ->first()
                ?->calculateBalance()
                ->save();
        }
    }

    public function syncLedgerBooking(): void
    {
        $installment = $this->loanInstallment()->first();
        $loan = $installment?->loan;
        $transaction = $this->transaction()->first();
        $bankLedgerAccountId = $transaction?->bankConnection?->ledger_account_id;
        $amount = bcround((string) $this->amount, 2);

        if (! $this->is_accepted
            || ! $loan?->ledger_account_id
            || ! $bankLedgerAccountId
            || bccomp($amount, '0', 2) === 0
        ) {
            $this->deleteLedgerBookings();

            return;
        }

        $isRepayment = bccomp($amount, '0', 2) === -1;
        $loanSideColumn = $isRepayment ? 'debit_ledger_account_id' : 'credit_ledger_account_id';
        $shares = $this->ledgerShares($installment, $loan, ltrim($amount, '-'));

        foreach ($shares as $ledgerAccountId => $share) {
            if ($ledgerAccountId === $bankLedgerAccountId) {
                continue;
            }

            $data = [
                'debit_ledger_account_id' => $isRepayment ? $ledgerAccountId : $bankLedgerAccountId,
                'credit_ledger_account_id' => $isRepayment ? $bankLedgerAccountId : $ledgerAccountId,
                'amount' => $share,
                'booking_date' => $transaction->booking_date?->toDateString(),
                'booking_text' => $loan->name,
                'note' => $this->note,
            ];

            $ledgerBooking = $this->ledgerBookings()
                ->where($loanSideColumn, $ledgerAccountId)
                ->first();

            if ($ledgerBooking) {
                UpdateLedgerBooking::make(array_merge($data, ['id' => $ledgerBooking->getKey()]))
                    ->validate()
                    ->execute();

                continue;
            }

            CreateLedgerBooking::make(
                array_merge($data, [
                    'tenant_id' => $loan->tenant_id,
                    'source_type' => morph_alias(static::class),
                    'source_id' => $this->getKey(),
                ])
            )
                ->validate()
                ->execute();
        }

        $this->ledgerBookings()
            ->whereNotIn($loanSideColumn, array_keys($shares))
            ->get()
            ->each(fn (LedgerBooking $ledgerBooking) => $this->deleteLedgerBooking($ledgerBooking));
    }

    public function deleteLedgerBookings(): void
    {
        $this->ledgerBookings()
            ->get()
            ->each(fn (LedgerBooking $ledgerBooking) => $this->deleteLedgerBooking($ledgerBooking));
    }

    protected function deleteLedgerBooking(LedgerBooking $ledgerBooking): void
    {
        DeleteLedgerBooking::make(['id' => $ledgerBooking->getKey()])
            ->validate()
            ->execute();
    }

    protected function ledgerShares(LoanInstallment $installment, Loan $loan, string $absolute): array
    {
        $interest = bcround((string) $installment->interest_amount, 2);

        if (! $loan->interest_ledger_account_id
            || $loan->interest_ledger_account_id === $loan->ledger_account_id
            || bccomp($interest, '0', 2) < 1
        ) {
            return [$loan->ledger_account_id => $absolute];
        }

        $covered = bcround(
            (string) $this->newQuery()
                ->where('loan_installment_id', $installment->getKey())
                ->whereKeyNot($this->getKey())
                ->where('is_accepted', true)
                ->sum('amount'),
            2
        );
        $covered = ltrim($covered, '-');

        $openInterest = bccomp($covered, $interest, 2) === -1
            ? bcsub($interest, $covered, 2)
            : '0.00';

        $interestShare = bccomp($absolute, $openInterest, 2) === 1 ? $openInterest : $absolute;
        $principalShare = bcsub($absolute, $interestShare, 2);

        $shares = [];

        if (bccomp($interestShare, '0', 2) === 1) {
            $shares[$loan->interest_ledger_account_id] = $interestShare;
        }

        if (bccomp($principalShare, '0', 2) === 1) {
            $shares[$loan->ledger_account_id] = $principalShare;
        }

        return $shares;
    }
}
