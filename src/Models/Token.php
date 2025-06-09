<?php

namespace FluxErp\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\MassPrunable;
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

    public function hasExceededMaxUsage(): bool
    {
        $maxUsageLimit = $this->max_uses;
        $usageCount = $this->uses;

        return $this->hasMaxUsageLimit() && ($usageCount >= $maxUsageLimit);
    }

    public function hasExpired(): bool
    {
        return ! ($this->expires_at === null) && now()->gt($this->expires_at);
    }

    public function hasMaxUsageLimit(): bool
    {
        return $this->max_uses > 0;
    }

    public function hasUrlBinding(): bool
    {
        return $this->url !== null;
    }

    public function isValid(): bool
    {
        return ! ($this->hasExpired() || $this->hasExceededMaxUsage() || ! $this->matchesUrlBinding());
    }

    public function matchesUrlBinding(): bool
    {
        return $this->hasUrlBinding()
            && parse_url(request()->url(), PHP_URL_PATH) === parse_url($this->url, PHP_URL_PATH);
    }

    public function prunable(): Builder
    {
        return static::query()->invalid();
    }

    public function scopeInvalid(Builder $query): Builder
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

    public function scopeValid(Builder $query): Builder
    {
        return $query
            ->where(function ($query) {
                return $query
                    ->where('max_uses', '=', '0')
                    ->orWhereRaw('uses < max_uses');
            })
            ->where(function ($query) {
                return $query
                    ->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            });
    }

    public function use(): bool
    {
        if ($this->uses === null) {
            $this->uses = 1;
        } else {
            $this->increment('uses');
        }

        return $this->save();
    }
}
