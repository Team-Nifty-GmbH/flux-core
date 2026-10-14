<?php

namespace FluxErp\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Traits\HasRoles;

class Token extends FluxAuthenticatable
{
    use HasRoles, MassPrunable;

    public ?string $token = null;

    protected $guarded = [
        'id',
    ];

    protected static function booted(): void
    {
        static::deleting(function (Token $model): void {
            $model->tokens()->delete();
        });
    }

    protected function casts(): array
    {
        return [
            'abilities' => 'array',
            'expires_at' => 'datetime',
        ];
    }

    // Public methods
    public function hasExceededMaxUsage(): bool
    {
        $maxUsageLimit = $this->max_uses;
        $usageCount = $this->uses;

        return $this->hasMaxUsageLimit() && ($usageCount >= $maxUsageLimit);
    }

    public function hasExpired(): bool
    {
        return ! is_null($this->expires_at) && now()->gt($this->expires_at);
    }

    public function hasMaxUsageLimit(): bool
    {
        return $this->max_uses > 0;
    }

    public function hasUrlBinding(): bool
    {
        return ! is_null($this->url);
    }

    public function isValid(): bool
    {
        return ! ($this->hasExpired() || $this->hasExceededMaxUsage() || ! $this->matchesUrlBinding());
    }

    public function matchesUrlBinding(): bool
    {
        if (! $this->hasUrlBinding()) {
            return true;
        }

        return parse_url(request()->url(), PHP_URL_PATH) === parse_url($this->url, PHP_URL_PATH);
    }

    public function prunable(): Builder
    {
        return static::query()->invalid();
    }

    public function use(): bool
    {
        $claimed = static::query()
            ->whereKey($this->getKey())
            ->where(fn (Builder $query) => $query
                ->whereNull('max_uses')
                ->orWhere('max_uses', 0)
                ->orWhereRaw('COALESCE(uses, 0) < max_uses')
            )
            ->update(['uses' => DB::raw('COALESCE(uses, 0) + 1')]);

        if ($claimed) {
            $this->uses = ($this->uses ?? 0) + 1;
            $this->syncOriginalAttribute('uses');
        }

        return (bool) $claimed;
    }

    // Scopes
    protected function scopeInvalid(Builder $query): Builder
    {
        return $query
            ->where(function ($query) {
                return $query
                    ->where('max_uses', '>', '0')
                    ->whereRaw('uses >= max_uses');
            })
            ->orWhere(function ($query) {
                return $query
                    ->whereNotNull('expires_at')
                    ->where('expires_at', '<=', now());
            });
    }

    protected function scopeValid(Builder $query): Builder
    {
        return $query
            ->where(function (Builder $query) {
                return $query
                    ->where('max_uses', '=', '0')
                    ->orWhereRaw('uses < max_uses');
            })
            ->where(function (Builder $query) {
                return $query
                    ->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            });
    }
}
