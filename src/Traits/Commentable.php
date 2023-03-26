<?php

namespace FluxErp\Traits;

use FluxErp\Models\Comment;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait Commentable
{
    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'model')
            ->orderBy('id', 'desc')
            ->where('comments.parent_id', null)
            ->with('children');
    }
}
