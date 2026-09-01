<?php

namespace FluxErp\Models\Pivots;

use FluxErp\Models\MediaFolder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class MediaFolderModel extends FluxPivot
{
    protected $table = 'media_folder_model';

    // Relations
    /**
     * @return BelongsTo<MediaFolder, $this>
     */
    public function mediaFolder(): BelongsTo
    {
        return $this->belongsTo(MediaFolder::class, 'media_folder_id');
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function model(): MorphTo
    {
        return $this->morphTo('model');
    }
}
