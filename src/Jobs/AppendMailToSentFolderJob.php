<?php

namespace FluxErp\Jobs;

use FluxErp\Models\MailAccount;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AppendMailToSentFolderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public readonly MailAccount $mailAccount, public readonly string $message) {}

    public function handle(): void
    {
        $this->mailAccount->appendToSentFolder($this->message);
    }
}
