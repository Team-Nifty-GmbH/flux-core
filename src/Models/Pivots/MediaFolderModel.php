<?php

namespace FluxErp\Models\Pivots;

use FluxErp\Models\MediaFolder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class MediaFolderModel extends FluxPivot
{
    public $incrementing = true;

    public $timestamps = false;

    protected $primaryKey = 'pivot_id';

    protected $table = 'media_folder_model';

    public function mediaFolder(): BelongsTo
    {
        return $this->belongsTo(MediaFolder::class, 'media_folder_id');
    }

    public function model(): MorphTo
    {
        return $this->morphTo('model');
    }
}
