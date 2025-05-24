<?php

namespace FluxErp\Livewire\Widgets;

use FluxErp\Livewire\Dashboard\Dashboard;
use FluxErp\Models\Activity;
use FluxErp\Support\Widgets\ValueList;
use Illuminate\Support\Str;
use Livewire\Attributes\Renderless;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;

class RecentActivities extends ValueList
{
    public static function dashboardComponent(): string
    {
        return Dashboard::class;
    }

    #[Renderless]
    public function calculateList(): void
    {
        $this->items = resolve_static(Activity::class, 'query')
            ->with(['causer:id,name', 'subject'])
            ->whereNot('event', 'visit')
            ->latest()
            ->limit($this->limit)
            ->get()
            ->map(fn (Activity $item) => [
                'id' => $item->id,
                'label' => trim(
                    $item->causer?->name . ' '
                    . __(Str::headline($item->subject_type ?? ''))
                    . ' ' . __($item->description)
                ),
                'subLabel' => $item->subject && method_exists($item->subject, 'getLabel')
                    ? $item->subject->getLabel()
                    : null,
                'value' => $item->created_at
                    ->locale(app()->getLocale())
                    ->timezone(auth()->user()?->timezone ?? config('app.timezone'))
                    ->isoFormat('L LT'),
                'growthRate' => DataTableButton::make()
                    ->icon('eye')
                    ->when(fn () => $item->subject
                        && method_exists($item->subject, 'detailRoute')
                        && $item->subject->detailRoute()
                    )
                    ->attributes([
                        'wire:navigate' => true,
                    ])
                    ->href(
                        $item->subject
                            && method_exists($item->subject, 'detailRoute')
                            && $item->subject->detailRoute()
                        ? $item->subject->detailRoute()
                        : '#'
                    )
                    ->toHtml(),
            ])
            ->toArray();
    }

    #[Renderless]
    public function hasMore(): bool
    {
        return $this->limit < resolve_static(Activity::class, 'query')
            ->whereNot('event', 'visit')
            ->count();
    }

    #[Renderless]
    public function showMore(): void
    {
        $this->limit += 10;

        $this->calculateList();
    }

    protected function getListeners(): array
    {
        return [
            'echo-private:' . resolve_static(Activity::class, 'getBroadcastChannel')
                . ',.ActivityCreated' => 'calculateList',
        ];
    }

    protected function hasLoadMore(): bool
    {
        return true;
    }
}
