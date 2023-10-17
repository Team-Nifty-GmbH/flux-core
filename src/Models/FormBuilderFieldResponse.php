<?php

namespace FluxErp\Models;

use FluxErp\Traits\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormBuilderFieldResponse extends Model
{
    use SoftDeletes;

    protected $with = ['field'];

    protected $guarded = [];

    public function field(): BelongsTo
    {
        return $this->belongsTo(FormBuilderField::class);
    }

    public function parentResponse()
    {
        return $this->belongsTo(FormBuilderResponse::class, 'response_id', 'id');
    }

    public function form()
    {
        return $this->belongsTo(FormBuilderForm::class);
    }
}
