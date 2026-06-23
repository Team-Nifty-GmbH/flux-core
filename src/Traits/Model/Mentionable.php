<?php

namespace FluxErp\Traits\Model;

use FluxErp\States\State;
use FluxErp\Support\Mentions\MentionState;
use FluxErp\Traits\Scout\Searchable;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use TeamNiftyGmbH\DataTable\Contracts\InteractsWithDataTables;

trait Mentionable
{
    public static function mentionTypeKey(): string
    {
        return morph_alias(static::class);
    }

    public static function mentionTypeLabel(): string
    {
        return Str::headline(morph_alias(static::class));
    }

    public static function mentionTypeIcon(): string
    {
        return 'tag';
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
        if ($this instanceof InteractsWithDataTables) {
            return $this->getLabel() ?? '#' . $this->getKey();
        }

        return (string) ($this->name ?? $this->getKey());
    }

    public function getMentionUrl(): ?string
    {
        return $this instanceof InteractsWithDataTables ? $this->getUrl() : null;
    }

    public function getMentionState(): ?MentionState
    {
        $state = $this->state ?? null;

        if (! $state instanceof State) {
            return null;
        }

        return new MentionState(__(Str::headline((string) $state)), $state->color());
    }
}
