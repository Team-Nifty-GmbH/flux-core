<?php

namespace FluxErp\Models;

use FluxErp\Traits\Model\BroadcastsEvents;
use FluxErp\Traits\Model\HasModelPermission;
use FluxErp\Traits\Model\ResolvesRelationsThroughContainer;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Foundation\Auth\User;
use Laravel\Sanctum\HasApiTokens;
use ReflectionClass;
use Spatie\Activitylog\Models\Concerns\CausesActivity;

abstract class FluxAuthenticatable extends User
{
    use BroadcastsEvents, CausesActivity, HasApiTokens, HasModelPermission, ResolvesRelationsThroughContainer;

    // Relations
    public function deviceTokens(): MorphMany
    {
        return $this->morphMany(DeviceToken::class, 'authenticatable');
    }

    // Public methods
    public function resolveCollectionFromAttribute(): ?string
    {
        $parent = get_parent_class(static::class);

        if ($parent && (new ReflectionClass($parent))->isAbstract()) {
            return null;
        }

        return parent::resolveCollectionFromAttribute();
    }
}
