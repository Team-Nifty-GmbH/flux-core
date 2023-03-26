<?php

namespace FluxErp\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class Token extends Authenticatable
{
    use HasApiTokens, HasRoles, MassPrunable;

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    protected $guarded = [
        'id',
        'uuid',
        'created_at',
        'created_by',
        'updated_at',
        'updated_by',
    ];

    public static function boot()
    {
        parent::boot();

        static::deleting(function (Token $model) {
            $model->tokens()->delete();
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

    public function hasExpired(): bool
    {
        return ! ($this->expires_at === null) && now()->gt($this->expires_at);
    }

    public function hasUrlBinding(): bool
    {
        return $this->url !== null;
    }

    public function matchesUrlBinding(): bool
    {
        return $this->hasUrlBinding()
            && parse_url(request()->url(), PHP_URL_PATH) === parse_url($this->url, PHP_URL_PATH);
    }

    public function hasMaxUsageLimit(): bool
    {
        return $this->max_uses > 0;
    }

    public function hasExceededMaxUsage(): bool
    {
        $maxUsageLimit = $this->max_uses;
        $usageCount = $this->uses;

        return $this->hasMaxUsageLimit() && ($usageCount >= $maxUsageLimit);
    }

    public function isValid(): bool
    {
        return ! ($this->hasExpired() || $this->hasExceededMaxUsage() || ! $this->matchesUrlBinding());
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

    public function prunable()
    {
        return static::query()->invalid();
    }
}
