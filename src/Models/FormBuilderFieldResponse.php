<?php

namespace FluxErp\Models;

use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormBuilderFieldResponse extends Model
{
    use SoftDeletes;
    use HasPackageFactory;

    protected $with = ['field'];

    protected $guarded = [];

    public function field(): BelongsTo
    {
        return $this->belongsTo(FormBuilderField::class);
    }

    public function form()
    {
        return $this->belongsTo(FormBuilderForm::class);
    }
}
