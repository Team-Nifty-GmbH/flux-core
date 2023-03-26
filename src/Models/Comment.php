<?php

namespace FluxErp\Models;

use FluxErp\Traits\BroadcastsEvents;
use FluxErp\Traits\HasUserModification;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\InteractsWithMedia;
use FluxErp\Traits\SoftDeletes;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Database\Eloquent\Casts\Attribute;
use FluxErp\Traits\HasPackageFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Spatie\MediaLibrary\HasMedia;

class Comment extends Model implements HasMedia
{
    use BroadcastsEvents, HasPackageFactory, HasUserModification, HasUuid, InteractsWithMedia, SoftDeletes;

    protected $appends = [
        'user',
    ];

    protected $hidden = [
        'model_type',
    ];

    protected $casts = [
        'uuid' => 'string',
    ];

    protected $guarded = [
        'id',
        'uuid',
    ];

    public function children(): hasMany
    {
        return $this->hasMany(Comment::class, 'parent_id')->with('children');
    }

    public function model(): MorphTo
    {
        return $this->morphTo('model');
    }

    public function user(): Attribute
    {
        $user = $this->created_by?->only('id', 'name', 'email', 'user_code');
        $user['avatar_url'] = $this->created_by?->getAvatarUrl();

        return Attribute::get(fn () => $user);
    }

    public static function restoring($callback): void
    {
        static::registerModelEvent('restoring', $callback);
    }

    /**
     * Get the channels that model events should broadcast on.
     *
     * @param  string  $event
     */
    public function broadcastOn($event): PrivateChannel
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
}
