<?php

namespace FluxErp\Tests\Fixtures;

use FluxErp\Traits\Model\Mentionable;
use Illuminate\Database\Eloquent\Model;
use TeamNiftyGmbH\DataTable\Contracts\InteractsWithDataTables;

class DataTableMentionFixture extends Model implements InteractsWithDataTables
{
    use Mentionable;

    protected $table = 'mentionable_fixtures';

    protected $guarded = [];

    public function getAvatarUrl(): ?string
    {
        return null;
    }

    public function getDescription(): ?string
    {
        return null;
    }

    public function getLabel(): ?string
    {
        return $this->label_value;
    }

    public function getUrl(): ?string
    {
        return $this->url_value;
    }
}
