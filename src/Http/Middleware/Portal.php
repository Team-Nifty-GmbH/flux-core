<?php

namespace FluxErp\Http\Middleware;

use Closure;
use FluxErp\Models\Address;
use FluxErp\Models\Order;
use FluxErp\Models\OrderPosition;
use FluxErp\Models\SerialNumber;
use FluxErp\Models\Ticket;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;

class Portal
{
    public function handle(Request $request, Closure $next): mixed
    {
        if (request()->isPortal()) {
            SerialNumber::addGlobalScope('portal', function (Builder $query) {
                $query->whereHas(
                    'orderPosition.order',
                    fn (Builder $query) => $query->where('contact_id', auth()->user()->contact->id)
                );
            });
            Order::addGlobalScope('portal', function (Builder $query) {
                $query->where('contact_id', auth()->user()->contact->id);
            });
            OrderPosition::addGlobalScope('portal', function (Builder $query) {
                $query->whereRelation('order', 'contact_id', auth()->user()->contact->id);
            });
            Ticket::addGlobalScope('portal', function (Builder $query) {
                $query->where('authenticatable_type', Relation::getMorphClassAlias(Address::class))
                    ->where('authenticatable_id', auth()->user()->id);
            });

            config(['livewire.layout' => 'flux::components.layouts.portal']);
            config(['app.url' => config('flux.portal_domain')]);
        }

        return $next($request);
    }
}
