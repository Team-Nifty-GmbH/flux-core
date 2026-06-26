<?php

namespace FluxErp\Http\Controllers;

use FluxErp\Helpers\ResponseHelper;
use FluxErp\Models\Ticket;
use FluxErp\States\Ticket\TicketState;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    public function userIndex(Request $request): JsonResponse
    {
        $endStates = TicketState::all()
            ->filter(fn (string $state): bool => $state::$isEndState)
            ->keys()
            ->toArray();

        $tickets = $request->user()
            ->tickets()
            ->whereNotIn('state', $endStates)
            ->orderByRaw("state = 'escalated' DESC")
            ->orderBy('created_at')
            ->get(['id', 'ticket_number', 'title', 'state'])
            ->map(fn (Ticket $ticket): array => [
                'id' => $ticket->getKey(),
                'ticket_number' => $ticket->ticket_number,
                'title' => $ticket->title,
                'state' => $ticket->state::$name,
                'url' => $ticket->getUrl(),
            ]);

        return ResponseHelper::createResponseFromBase(statusCode: 200, data: $tickets)
            ->setEncodingOptions(JSON_UNESCAPED_SLASHES);
    }
}
