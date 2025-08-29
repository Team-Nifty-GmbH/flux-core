<?php

namespace FluxErp\Models\Pivots;

class UserWorkTimeModel extends FluxPivot
{
    protected $fillable = [
        'user_id',
        'work_time_model_id',
    ];

    protected $table = 'user_work_time_model';
}
