<?php

namespace FluxErp\Livewire\Widgets;

use FluxErp\Contracts\HasWidgetOptions;
use FluxErp\Livewire\Dashboard\Dashboard;
use FluxErp\Livewire\DataTables\ContactList;
use FluxErp\Livewire\Support\Widgets\Charts\CircleChart;
use FluxErp\Models\Contact;
use FluxErp\Traits\Livewire\IsTimeFrameAwareWidget;
use FluxErp\Traits\Widgetable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Renderless;
use Livewire\Livewire;
use TeamNiftyGmbH\DataTable\Helpers\SessionFilter;

class ContactsByContactOrigin extends CircleChart implements HasWidgetOptions
{
    use IsTimeFrameAwareWidget, Widgetable;

    public ?array $chart = [
        'type' => 'donut',
    ];

    #[Locked]
    public array $data = [];

    public bool $showTotals = false;

    public static function dashboardComponent(): array|string
    {
        return Dashboard::class;
    }

    #[Renderless]
    public function calculateByTimeFrame(): void
    {
        $this->calculateChart();
        $this->updateData();
    }

    public function calculateChart(): void
    {
        $this->data = resolve_static(Contact::class, 'query')
            ->whereBetween('created_at', [
                $this->getStart()->toDateTimeString(),
                $this->getEnd()->toDateTimeString(),
            ])
            ->groupBy('record_origin_id')
            ->with('recordOrigin:id,name,is_active')
            ->selectRaw('record_origin_id, COUNT(id) as total')
            ->orderBy('total', 'desc')
            ->get()
            ->map(fn (Model $contact) => $contact->recordOrigin?->is_active !== false
                ? [
                    'id' => $contact->record_origin_id,
                    'label' => $contact->recordOrigin?->name ?? __('Unassigned Contacts'),
                    'total' => $contact->total,
                ] : null,
            )
            ->filter()
            ->toArray();

        $this->labels = array_column($this->data, 'label');
        $this->series = array_column($this->data, 'total');
    }

    public function getPlotOptions(): array
    {
        return [
            'pie' => [
                'donut' => [
                    'labels' => [
                        'show' => true,
                        'total' => [
                            'show' => true,
                            'label' => __('Total'),
                        ],
                    ],
                ],
            ],
        ];
    }

    #[Renderless]
    public function options(): array
    {
        return array_map(
            fn (array $data) => [
                'label' => data_get($data, 'label'),
                'method' => 'show',
                'params' => [
                    'id' => data_get($data, 'id'),
                    'label' => data_get($data, 'label'),
                ],
            ],
            $this->data
        );
    }

    #[Renderless]
    public function show(array $contactOrigin): void
    {
        $start = $this->getStart()->toDateString();
        $end = $this->getEnd()->toDateString();

        SessionFilter::make(
            Livewire::new(resolve_static(ContactList::class, 'class'))->getCacheKey(),
            fn (Builder $query) => $query
                ->where('contact_origin_id', data_get($contactOrigin, 'id'))
                ->whereBetween('created_at', [$start, $end]),
            __('Contacts by :contact-origin', ['contact-origin' => data_get($contactOrigin, 'label')]),
        )->store();

        $this->redirectRoute('contacts.contacts', navigate: true);
    }

    public function showTitle(): bool
    {
        return true;
    }
}
