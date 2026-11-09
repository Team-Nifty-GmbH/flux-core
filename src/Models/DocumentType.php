<?php

namespace FluxErp\Models;

use FluxErp\Traits\Model\HasPackageFactory;
use FluxErp\Traits\Model\HasUserModification;
use FluxErp\Traits\Model\LogsActivity;

class DocumentType extends FluxModel
{
    use HasPackageFactory, HasUserModification, LogsActivity;

    protected function casts(): array
    {
        return [
            'model_types' => 'array',
            'retention_years' => 'integer',
            'is_owner_uploadable' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function isAllowedFor(string $morphAlias): bool
    {
        return ! $this->model_types || in_array($morphAlias, $this->model_types, true);
    }
}
