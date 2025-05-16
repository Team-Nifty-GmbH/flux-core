<?php

namespace FluxErp\Livewire\Ticket;

use FluxErp\Livewire\Features\Comments\Comments as BaseComments;

class Comments extends BaseComments
{
    public string $modelType = \FluxErp\Models\Ticket::class;

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
