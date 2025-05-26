<?php

namespace FluxErp\Livewire\Ticket;

use FluxErp\Livewire\Support\Comments as BaseComments;
use FluxErp\Models\Ticket;
use Livewire\Attributes\Renderless;

class Comments extends BaseComments
{
    protected string $modelType = Ticket::class;

    #[Renderless]
    public function saveComment(array $comment, array $files = []): ?array
    {
        $result = parent::saveComment($comment, $files);

        $this->js(<<<'JS'
            $wire.$parent.fetchTicket();
        JS);

        return $result;
    }
}
