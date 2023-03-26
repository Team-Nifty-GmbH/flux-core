<?php

namespace FluxErp\Services;

use FluxErp\Helpers\ResponseHelper;
use FluxErp\Models\AdditionalColumn;
use FluxErp\Models\Ticket;
use FluxErp\Models\TicketType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;

class TicketService
{
    public function create(array $data): Ticket
    {
        $users = Arr::pull($data, 'users');

        $ticket = new Ticket($data);

        if ($ticket->ticket_type_id) {
            $meta = $ticket->getDirtyMeta();

            $additionalColumns = Arr::keyBy(
                AdditionalColumn::query()
                    ->where('model_type', TicketType::class)
                    ->where('model_id', $ticket->ticket_type_id)
                    ->select(['id', 'name'])
                    ->get()
                    ->toArray(),
                'name'
            );

            foreach ($meta as $key => $item) {
                if (array_key_exists($key, $additionalColumns)) {
                    $item->forceType($ticket->ticketType->getCastForMetaKey($key))
                        ->forceFill([
                            'additional_column_id' => $additionalColumns[$key]['id'],
                        ]);

                    $ticket->setMetaChanges($meta->put($key, $item));
                }
            }
        }

        $ticket->getSerialNumber('ticket_number', Auth::user()?->client_id);

        $ticket->save();

        if (is_array($users)) {
            $ticket->users()->sync($users);
        }

        return $ticket->refresh();
    }

    public function update(array $data): Model
    {
        $users = Arr::pull($data, 'users');

        $ticket = Ticket::query()
            ->whereKey($data['id'])
            ->first();

        $ticket->fill($data);
        $ticket->save();

        if (is_array($users)) {
            $ticket->users()->sync($users);
        }

        return $ticket->refresh();
    }

    public function delete(string $id): array
    {
        $ticket = Ticket::query()
            ->whereKey($id)
            ->first();

        if (! $ticket) {
            return ResponseHelper::createArrayResponse(
                statusCode: 404,
                data: ['id' => 'ticket not found']
            );
        }

        $ticket->delete();

        return ResponseHelper::createArrayResponse(
            statusCode: 204,
            statusMessage: 'ticket deleted'
        );
    }

    public function toggleUserAssignment(array $data): array
    {
        $ticket = Ticket::query()
            ->whereKey($data['ticket_id'])
            ->first();

        $users = $ticket->users()->pluck('id')->toArray();
        if (! in_array($data['user_id'], $users)) {
            $ticket->users()->attach($data['user_id']);
            $statusMessage = 'user attached';
        } else {
            $ticket->users()->detach($data['user_id']);
            $statusMessage = 'user detached';
        }

        return ResponseHelper::createArrayResponse(
            statusCode: 200,
            statusMessage: $statusMessage
        );
    }
}
