<?php

namespace FluxErp\Models\Pivots;

use FluxErp\Models\MailAccount;
use FluxErp\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MailAccountUser extends FluxPivot
{
    public $incrementing = true;

    public $timestamps = false;

    protected $primaryKey = 'id';

    protected $table = 'mail_account_user';

    public function mailAccount(): BelongsTo
    {
        return $this->belongsTo(MailAccount::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
