<?php

namespace FluxErp\Models;

use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\InteractsWithMedia;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Str;
use Laravel\Scout\Searchable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\Tags\HasTags;
use TeamNiftyGmbH\DataTable\Traits\BroadcastsEvents;

class MailMessage extends Model implements HasMedia
{
    use BroadcastsEvents, HasPackageFactory, HasTags, HasUuid, InteractsWithMedia, Searchable;

    protected $guarded = [
        'id',
    ];

    protected $casts = [
        'from' => 'array',
        'to' => 'array',
        'cc' => 'array',
        'bcc' => 'array',
        'is_seen' => 'boolean',
    ];

    public static function booted()
    {
        static::saving(function (MailMessage $message) {
            if ($message->isDirty('text_body')) {
                $message->text_body = strip_tags($message->text_body ?? '');
            }
        });
    }

    protected function fromMail(): Attribute
    {
        return Attribute::get(
            fn ($value) => Str::between($this->from ?? '', '<', '>') ?: $this->from ?: null
        );
    }

    public function addresses(): MorphToMany
    {
        return $this->morphedByMany(Address::class, 'mailable');
    }

    public function mailFolder(): BelongsTo
    {
        return $this->belongsTo(MailFolder::class);
    }

    public function mailAccount(): BelongsTo
    {
        return $this->belongsTo(MailAccount::class);
    }

    public function orders(): MorphToMany
    {
        return $this->morphedByMany(Order::class, 'mailable');
    }

    public function toSearchableArray(): array
    {
        $array = $this->toArray();
        unset($array['html_body']);

        return $array;
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('message')
            ->acceptsMimeTypes(['application/pdf'])
            ->useDisk('local')
            ->singleFile();

        $this->addMediaCollection('attachments')
            ->useDisk('local');
    }
}
