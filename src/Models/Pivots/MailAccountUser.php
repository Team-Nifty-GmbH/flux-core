<?php

namespace FluxErp\Models\Pivots;

use FluxErp\Models\MailAccount;
use FluxErp\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MailAccountUser extends FluxPivot
{
    protected $table = 'mail_account_user';

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
        ];
    }

    // Relations
    /**
     * @return BelongsTo<MailAccount, $this>
     */
    public function mailAccount(): BelongsTo
    {
        return $this->belongsTo(MailAccount::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
