<?php

namespace FluxErp\Models;

use FluxErp\Enums\OrderTypeEnum;
use FluxErp\States\AbsenceRequest\AbsenceRequestState;
use FluxErp\Traits\Approvable;
use FluxErp\Traits\HasClientAssignment;
use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasUserModification;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\SoftDeletes;
use FluxErp\Traits\Trackable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AbsenceRequest extends FluxModel
{
    use HasClientAssignment, HasPackageFactory, HasUserModification, HasUuid, SoftDeletes, Trackable;

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'days_requested' => 'decimal:2',
        'approved_at' => 'datetime',
        'is_emergency' => 'boolean',
    ];

    protected $attributes = [
        'status' => 'draft',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function absenceType(): BelongsTo
    {
        return $this->belongsTo(AbsenceType::class);
    }

    public function substituteUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'substitute_user_id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function workTimes(): HasMany
    {
        return $this->hasMany(WorkTime::class, 'vacation_request_id');
    }

    public function calculateDays(): float
    {
        $days = 0;
        $current = $this->start_date->copy();
        
        while ($current <= $this->end_date) {
            if ($current->isWeekday()) {
                if ($current->isSameDay($this->start_date) && $this->start_half_day !== 'full') {
                    $days += 0.5;
                } elseif ($current->isSameDay($this->end_date) && $this->end_half_day !== 'full') {
                    $days += 0.5;
                } else {
                    $days += 1;
                }
            }
            $current->addDay();
        }
        
        return $days;
    }

    public function canApprove(User $user = null): bool
    {
        $user = $user ?? auth()->user();
        
        if ($this->status !== 'pending') {
            return false;
        }
        
        if ($user->id === $this->user_id) {
            return false;
        }
        
        return $user->hasPermissionTo('vacation-request.approve') 
            || $user->id === $this->user->supervisor_id;
    }

    public function approve(User $approver, ?string $note = null): bool
    {
        if (! $this->canApprove($approver)) {
            return false;
        }
        
        $this->status = 'approved';
        $this->approved_by = $approver->id;
        $this->approved_at = now();
        $this->approval_note = $note;
        
        return $this->save();
    }

    public function reject(User $approver, string $reason): bool
    {
        if (! $this->canApprove($approver)) {
            return false;
        }
        
        $this->status = 'rejected';
        $this->approved_by = $approver->id;
        $this->approved_at = now();
        $this->rejection_reason = $reason;
        
        return $this->save();
    }

    public function submit(): bool
    {
        if ($this->status !== 'draft') {
            return false;
        }
        
        $this->status = 'pending';
        $this->days_requested = $this->calculateDays();
        
        return $this->save();
    }

    public function cancel(): bool
    {
        if (! in_array($this->status, ['draft', 'pending'])) {
            return false;
        }
        
        $this->status = 'cancelled';
        
        return $this->save();
    }

    public function hasConflicts(): bool
    {
        return resolve_static(AbsenceRequest::class, 'query')
            ->where('user_id', $this->user_id)
            ->where('id', '!=', $this->id)
            ->whereIn('status', ['pending', 'approved'])
            ->where(function ($query) {
                $query->whereBetween('start_date', [$this->start_date, $this->end_date])
                    ->orWhereBetween('end_date', [$this->start_date, $this->end_date])
                    ->orWhere(function ($q) {
                        $q->where('start_date', '<=', $this->start_date)
                            ->where('end_date', '>=', $this->end_date);
                    });
            })
            ->exists();
    }

    public function isInBlackoutPeriod(): bool
    {
        $user = $this->user;
        
        return resolve_static(VacationBlackout::class, 'query')
            ->where('is_active', true)
            ->where('client_id', $this->client_id)
            ->where(function ($query) {
                $query->whereBetween('start_date', [$this->start_date, $this->end_date])
                    ->orWhereBetween('end_date', [$this->start_date, $this->end_date])
                    ->orWhere(function ($q) {
                        $q->where('start_date', '<=', $this->start_date)
                            ->where('end_date', '>=', $this->end_date);
                    });
            })
            ->where(function ($query) use ($user) {
                $query->whereHas('users', function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                })->orWhereHas('roles', function ($q) use ($user) {
                    $q->whereIn('role_id', $user->roles->pluck('id'));
                });
            })
            ->exists();
    }
}