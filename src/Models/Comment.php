<?php

namespace FluxErp\Models;

use FluxErp\Traits\HasNotificationSubscriptions;
use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasParentChildRelations;
use FluxErp\Traits\HasUserModification;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\InteractsWithMedia;
use FluxErp\Traits\LogsActivity;
use FluxErp\Traits\Notifiable;
use FluxErp\Traits\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Spatie\MediaLibrary\HasMedia;

class Comment extends FluxModel implements HasMedia
{
    use HasNotificationSubscriptions, HasPackageFactory, HasParentChildRelations, HasUserModification, HasUuid, InteractsWithMedia, LogsActivity,
        SoftDeletes;

    protected $appends = [
        'user',
    ];

    protected $hidden = [
        'model_type',
    ];

    protected $guarded = [
        'id',
    ];

    protected static function booted(): void
    {
        static::saving(function (Comment $comment) {
            if ($comment->isDirty('comment')) {
                preg_match_all('/data-id="([^:]+:\d+)"/', $comment->comment, $matches);
                collect(data_get($matches, 1, []))
                    ->map(fn (string $mention) => morph_to($mention))
                    ->filter() // filter null values if morph was not possible
                    ->filter(function (Model $notifiable) {
                        return in_array(Notifiable::class, class_uses_recursive($notifiable));
                    })
                    ->each(function (Model $notifiable) use ($comment) {
                        $notifiable->subscribeNotificationChannel($comment->broadcastChannel());
                    });
            }
        });
    }

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

    public static function restoring($callback): void
    {
        static::registerModelEvent('restoring', $callback);
    }

    public function broadcastChannel(): string
    {
        return str_replace('\\', '.', morphed_model($this->model_type)) . '.' . $this->model_id;
    }

    public static function getGenericChannelEvents(): array
    {
        return [];
    }

    public function broadcastWith(): array
    {
        $data = $this->toArray();
        $data['user'] = $this->user;

        return ['model' => $data];
    }
}
