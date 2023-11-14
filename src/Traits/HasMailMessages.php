<?php

namespace FluxErp\Traits;

use FluxErp\Models\MailMessage;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

trait HasMailMessages
{
    public function mailMessages(): MorphToMany
    {
        return $this->morphToMany(MailMessage::class, 'mailable');
    }
}
