<?php

namespace FluxErp\Models;

use FluxErp\Contracts\IsSubscribable;
use FluxErp\Contracts\MentionsContent;
use FluxErp\Traits\Model\HasPackageFactory;
use FluxErp\Traits\Model\HasParentChildRelations;
use FluxErp\Traits\Model\HasUserModification;
use FluxErp\Traits\Model\HasUuid;
use FluxErp\Traits\Model\InteractsWithMedia;
use FluxErp\Traits\Model\LogsActivity;
use FluxErp\Traits\Model\RecordsMentions;
use FluxErp\Traits\Model\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Spatie\MediaLibrary\HasMedia;

class Comment extends FluxModel implements HasMedia, IsSubscribable, MentionsContent
{
    use HasPackageFactory, HasParentChildRelations, HasUserModification, HasUuid, InteractsWithMedia, LogsActivity,
        RecordsMentions, SoftDeletes;

    protected $appends = [
        'user',
    ];

    protected $hidden = [
        'model_type',
    ];

    // Public static methods
    public static function getGenericChannelEvents(): array
    {
        return [];
    }

    public static function restoring($callback): void
    {
        static::registerModelEvent('restoring', $callback);
    }

    protected function casts(): array
    {
        return [
            'is_internal' => 'boolean',
            'is_sticky' => 'boolean',
        ];
    }

    // Relations
    public function model(): MorphTo
    {
        return $this->morphTo('model');
    }

    // Public methods
    public function broadcastChannel(): string
    {
        return $this->model_type . '.' . $this->model_id;
    }

    public function broadcastWith(): array
    {
        $data = $this->toArray();
        $data['user'] = $this->user;

        return ['model' => $data];
    }

    /**
     * @return array<int, string>
     */
    public function mentionableColumns(): array
    {
        return ['comment'];
    }

    // Attributes
    protected function user(): Attribute
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
