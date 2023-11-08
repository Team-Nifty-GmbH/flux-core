<?php

namespace FluxErp\Models;

use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormBuilderFieldResponse extends Model
{
    use SoftDeletes;
    use HasPackageFactory;
    use HasUuid;

    protected $with = ['field'];

    protected $guarded = ['id'];

    public function field(): BelongsTo
    {
        return $this->belongsTo(FormBuilderField::class, 'field_id');
    }

    public function form(): BelongsTo
    {
        return $this->belongsTo(FormBuilderForm::class, 'form_id');
    }
}
