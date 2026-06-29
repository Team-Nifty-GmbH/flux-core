<?php

namespace FluxErp\Traits\Model;

use FluxErp\Enums\MentionTypeEnum;
use FluxErp\States\State;
use FluxErp\Support\Mentions\MentionState;
use FluxErp\Traits\Scout\Searchable;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

trait Mentionable
{
    public static function mentionType(): string
    {
        return MentionTypeEnum::Record;
    }

    public static function mentionTypeIcon(): string
    {
        return 'tag';
    }

    public static function mentionTypeKey(): string
    {
        return morph_alias(static::class);
    }

    public static function mentionTypeLabel(): string
    {
        return Str::headline(morph_alias(static::class));
    }

    /**
     * @return Collection<int, static>
     */
    public static function searchMentionCandidates(string $query, int $limit = 5): Collection
    {
        $trimmed = trim($query);
        if ($trimmed === '') {
            return collect();
        }

        if (in_array(Searchable::class, class_uses_recursive(static::class), true)) {
            return static::search($trimmed)->take($limit)->get();
        }

        return static::query()
            ->where('name', 'like', $trimmed . '%')
            ->limit($limit)
            ->get();
    }

    public function getMentionLabel(): string
    {
        if (method_exists($this, 'getLabel')) {
            return $this->getLabel() ?? '#' . $this->getKey();
        }

        return '#' . $this->getKey();
    }

    public function getMentionState(): ?MentionState
    {
        $column = $this->mentionStateColumn();
        $state = $column !== null ? ($this->{$column} ?? null) : null;

        if (! $state instanceof State) {
            return null;
        }

        return MentionState::make(__(Str::headline((string) $state)), $state->color());
    }

    public function getMentionUrl(): ?string
    {
        return method_exists($this, 'getUrl') ? $this->getUrl() : null;
    }

    public function mentionStateColumn(): ?string
    {
        return 'state';
    }
}
