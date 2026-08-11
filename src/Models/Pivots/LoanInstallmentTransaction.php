<?php

namespace FluxErp\Models\Pivots;

use FluxErp\Actions\LedgerBooking\CreateLedgerBooking;
use FluxErp\Actions\LedgerBooking\DeleteLedgerBooking;
use FluxErp\Actions\LedgerBooking\UpdateLedgerBooking;
use FluxErp\Models\LedgerBooking;
use FluxErp\Models\LoanInstallment;
use FluxErp\Models\Transaction;
use FluxErp\Traits\Model\HasPackageFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;

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
            $loanInstallmentTransaction->deleteLedgerBooking();
        });
    }

    protected function casts(): array
    {
        return [
            'is_accepted' => 'boolean',
        ];
    }

    public function ledgerBooking(): MorphOne
    {
        return $this->morphOne(LedgerBooking::class, 'source');
    }

    public function loanInstallment(): BelongsTo
    {
        return $this->belongsTo(LoanInstallment::class);
    }

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
        $ledgerBooking = $this->ledgerBooking()->first();
        $loan = $this->loanInstallment()->first()?->loan;
        $transaction = $this->transaction()->first();
        $bankLedgerAccountId = $transaction?->bankConnection?->ledger_account_id;
        $amount = bcround((string) $this->amount, 2);

        if (! $this->is_accepted
            || ! $loan?->ledger_account_id
            || ! $bankLedgerAccountId
            || $loan->ledger_account_id === $bankLedgerAccountId
            || bccomp($amount, '0', 2) === 0
        ) {
            $this->deleteLedgerBooking($ledgerBooking);

            return;
        }

        $isRepayment = bccomp($amount, '0', 2) === -1;

        $data = [
            'debit_ledger_account_id' => $isRepayment ? $loan->ledger_account_id : $bankLedgerAccountId,
            'credit_ledger_account_id' => $isRepayment ? $bankLedgerAccountId : $loan->ledger_account_id,
            'amount' => ltrim($amount, '-'),
            'booking_date' => $transaction->booking_date?->toDateString(),
            'booking_text' => $loan->name,
            'note' => $this->note,
        ];

        if ($ledgerBooking) {
            UpdateLedgerBooking::make(array_merge($data, ['id' => $ledgerBooking->getKey()]))
                ->validate()
                ->execute();

            return;
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

    public function deleteLedgerBooking(?LedgerBooking $ledgerBooking = null): void
    {
        $ledgerBooking ??= $this->ledgerBooking()->first();

        if (! $ledgerBooking) {
            return;
        }

        DeleteLedgerBooking::make(['id' => $ledgerBooking->getKey()])
            ->validate()
            ->execute();
    }
}
