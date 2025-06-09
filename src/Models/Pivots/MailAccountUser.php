<?php

namespace FluxErp\Models\Pivots;

use FluxErp\Models\MailAccount;
use FluxErp\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MailAccountUser extends FluxPivot
{
    public $incrementing = true;

    public $timestamps = false;

    protected $primaryKey = 'id';

    protected $table = 'mail_account_user';

    public function mailAccount(): BelongsTo
    {
        return $this->belongsTo(MailAccount::class, 'mail_account_id');
    }

    public function siblings(): HasMany
    {
        return $this->hasMany(MailAccountUser::class, 'mail_account_id', 'mail_account_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
