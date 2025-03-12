<?php

namespace FluxErp\Http\Middleware;

use Closure;
use FluxErp\Models\Address;
use FluxErp\Models\Cart;
use FluxErp\Models\CartItem;
use FluxErp\Models\Media;
use FluxErp\Models\Order;
use FluxErp\Models\OrderPosition;
use FluxErp\Models\Scopes\AddressMediaScope;
use FluxErp\Models\SerialNumber;
use FluxErp\Models\Ticket;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class PortalMiddleware
{
    public function handle(Request $request, Closure $next): mixed
    {
        if (request()->isPortal()) {
            config(['filesystems.disks.public.url' => config('flux.portal_domain') . '/storage']);
            resolve_static(SerialNumber::class, 'addGlobalScope', [
                'scope' => 'portal',
                'implementation' => function (Builder $query): void {
                    $query
                        ->where(function (Builder $query): void {
                            $query->whereHas(
                                'addresses',
                                fn (Builder $query) => $query->where('contact_id', auth()->user()?->contact_id)
                            );
                        });
                },
            ]);
            resolve_static(Order::class, 'addGlobalScope', [
                'scope' => 'portal',
                'implementation' => function (Builder $query): void {
                    $query->whereNotNull('contact_id')
                        ->where('contact_id', auth()->user()?->contact_id)
                        ->where(fn (Builder $query) => $query->where('is_locked', true)
                            ->orWhere('is_imported', true)
                        );
                },
            ]);
            resolve_static(OrderPosition::class, 'addGlobalScope', [
                'scope' => 'portal',
                'implementation' => function (Builder $query): void {
                    $query->whereRelation('order', 'contact_id', auth()->user()?->contact_id);
                },
            ]);
            resolve_static(Ticket::class, 'addGlobalScope', [
                'scope' => 'portal',
                'implementation' => function (Builder $query): void {
                    $query->whereNotNull('authenticatable_id')
                        ->where('authenticatable_type', morph_alias(Address::class))
                        ->whereHasMorph(
                            'authenticatable',
                            [auth()->user()?->getMorphClass()],
                            function (Builder $query): void {
                                $query->where('contact_id', auth()->user()?->contact_id);
                            }
                        );
                },
            ]);
            resolve_static(Cart::class, 'addGlobalScope', [
                'scope' => 'portal',
                'implementation' => function (Builder $query): void {
                    $query->where(function (Builder $query): void {
                        $query->where(function (Builder $query): void {
                            $query->where('authenticatable_id', auth()->id())
                                ->where('authenticatable_type', auth()->user()?->getMorphClass());
                        })
                            ->orWhere('session_id', session()->id())
                            ->orWhere('is_portal_public', true);
                    });
                },
            ]);
            resolve_static(CartItem::class, 'addGlobalScope', [
                'scope' => 'portal',
                'implementation' => function (Builder $query): void {
                    $query->whereHas('cart', function (Builder $query): void {
                        $query->where(function (Builder $query): void {
                            $query->where(function (Builder $query): void {
                                $query->where('authenticatable_id', auth()->id())
                                    ->where('authenticatable_type', auth()->user()?->getMorphClass());
                            })
                                ->orWhere('session_id', session()->id())
                                ->orWhere('is_portal_public', true);
                        });
                    });
                },
            ]);
            resolve_static(Cart::class, 'deleting', [
                'callback' => function (Cart $cart) {
                    if (
                        (
                            is_null($cart->authenticatable_type)
                            && is_null($cart->authenticatable_id)
                            && $cart->session_id === session()->id()
                        )
                        || (
                            $cart->authenticatable_type === auth()->user()?->getMorphClass()
                            && $cart->authenticatable_id === auth()->id()
                        )
                    ) {
                        return $cart->deleteQuietly();
                    }

                    return false;
                },
            ]);

            resolve_static(Media::class, 'addGlobalScope', [
                'scope' => resolve_static(AddressMediaScope::class, 'class'),
            ]);

            config(['livewire.layout' => 'flux::components.layouts.portal']);
            config(['app.url' => config('flux.portal_domain')]);
        }

        return $next($request);
    }
}
