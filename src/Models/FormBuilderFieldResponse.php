<?php

namespace FluxErp\Models;

use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasUuid;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class FormBuilderFieldResponse extends FluxModel
{
    use HasPackageFactory, HasUuid, SoftDeletes;

    public function field(): BelongsTo
    {
        return $this->belongsTo(FormBuilderField::class, 'field_id');
    }

    public function form(): BelongsTo
    {
        return $this->belongsTo(FormBuilderForm::class, 'form_id');
    }
}
