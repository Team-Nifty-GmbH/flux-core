<?php

namespace FluxErp\Models;

use FluxErp\Traits\Filterable;
use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\Notifiable;
use FluxErp\Traits\ResolvesRelationsThroughContainer;
use FluxErp\Traits\Scout\Searchable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Permission\Models\Role as SpatieRole;
use TeamNiftyGmbH\DataTable\Contracts\InteractsWithDataTables;

class Role extends SpatieRole implements InteractsWithDataTables
{
    use Filterable, HasPackageFactory, Notifiable, ResolvesRelationsThroughContainer, Searchable;

    protected $guarded = [
        'id',
    ];

    protected $hidden = ['pivot'];

    public function getAvatarUrl(): ?string
    {
        return route('icons', ['name' => 'users']);
    }

    public function getDescription(): ?string
    {
        return null;
    }

    public function getLabel(): ?string
    {
        return $this->name;
    }

    public function getUrl(): ?string
    {
        return null;
    }

    public function notify($instance): void
    {
        foreach ($this->users as $user) {
            $user->notify($instance);
        }
    }

    public function notifyNow($instance, ?array $channels = null): void
    {
        foreach ($this->users as $user) {
            $user->notifyNow($instance, $channels);
        }
    }

    public function users(): BelongsToMany
    {
        return $this->morphedByMany(
            User::class,
            'model',
            config('permission.table_names.model_has_roles'),
            config('permission.column_names.role_pivot_key'),
            config('permission.column_names.model_morph_key')
        );
    }
}
