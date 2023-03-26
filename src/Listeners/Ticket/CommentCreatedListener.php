<?php

namespace FluxErp\Listeners\Ticket;

use FluxErp\Models\Address;
use FluxErp\Models\Comment;
use FluxErp\Models\Ticket;
use FluxErp\Models\User;
use FluxErp\States\Ticket\WaitingForCustomer;
use FluxErp\States\Ticket\WaitingForSupport;
use Illuminate\Support\Facades\Auth;

class CommentCreatedListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(Comment $comment): void
    {
        if ($comment->model_type !== Ticket::class) {
            return;
        }

        $ticket = $comment->model;

        if (Auth::user() instanceof Address
            && $ticket->state->canTransitionTo(WaitingForSupport::class)
        ) {
            $ticket->state->transitionTo(WaitingForSupport::class);
        } elseif (Auth::user() instanceof User
            && $ticket->state->canTransitionTo(WaitingForCustomer::class)
        ) {
            $ticket->state->transitionTo(WaitingForCustomer::class);
        }
    }
}
