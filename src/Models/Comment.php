<?php

namespace FluxErp\Models;

use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasParentChildRelations;
use FluxErp\Traits\HasUserModification;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\InteractsWithMedia;
use FluxErp\Traits\LogsActivity;
use FluxErp\Traits\SoftDeletes;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Spatie\MediaLibrary\HasMedia;

class Comment extends FluxModel implements HasMedia
{
    use HasPackageFactory, HasParentChildRelations, HasUserModification, HasUuid, InteractsWithMedia, LogsActivity,
        SoftDeletes;

    protected $appends = [
        'user',
    ];

    protected $hidden = [
        'model_type',
    ];

    public static function restoring($callback): void
    {
        static::registerModelEvent('restoring', $callback);
    }

    /**
     * Get the channels that model events should broadcast on.
     *
     * @param  string  $event
     */
    public function broadcastOn($event): array|Channel
    {
        return new PrivateChannel(
            str_replace('\\', '.', $this->model_type) . '.' . $this->model_id
        );
    }

    public function broadcastWith(): array
    {
        $data = $this->toArray();
        $data['user'] = $this->user;

        return ['model' => $data];
    }

    public function model(): MorphTo
    {
        return $this->morphTo('model');
    }

    public function user(): Attribute
    {
        $user = $this->getCreatedBy();

        $userData = null;
        if ($user) {
            $userData = $user->only('id', 'name', 'email', 'user_code');
            $userData['avatar_url'] = method_exists($user, 'getAvatarUrl')
                ? $user->getAvatarUrl()
                : null;
        }

        return Attribute::get(fn () => $userData);
    }
}
