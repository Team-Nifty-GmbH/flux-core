<?php

namespace FluxErp\Models;

use FluxErp\Traits\ResolvesRelationsThroughContainer;
use FluxErp\Traits\Scout\Searchable;
use Spatie\Tags\Tag as BaseTag;
use TeamNiftyGmbH\DataTable\Contracts\InteractsWithDataTables;

class Tag extends BaseTag implements InteractsWithDataTables
{
    use ResolvesRelationsThroughContainer, Searchable;

    public function getLabel(): ?string
    {
        return $this->name;
    }

    public function getDescription(): ?string
    {
        return $this->slug;
    }

    public function getUrl(): ?string
    {
        return null;
    }

    public function getAvatarUrl(): ?string
    {
        return null;
    }
}
