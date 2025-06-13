<?php

namespace FluxErp\Livewire\Widgets;

use FluxErp\Contracts\HasWidgetOptions;
use FluxErp\Livewire\Dashboard\Dashboard;
use FluxErp\Livewire\DataTables\ContactList;
use FluxErp\Livewire\Support\Widgets\Charts\CircleChart;
use FluxErp\Models\Contact;
use FluxErp\Support\Metrics\Charts\Donut;
use FluxErp\Traits\Livewire\IsTimeFrameAwareWidget;
use FluxErp\Traits\Widgetable;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Renderless;
use Livewire\Livewire;
use TeamNiftyGmbH\DataTable\Helpers\SessionFilter;

class ContactsByContactOrigin extends CircleChart implements HasWidgetOptions
{
    use IsTimeFrameAwareWidget, Widgetable;

    public ?array $chart = [
        'type' => 'donut',
    ];

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
        $assignedMetrics = Donut::make(
            resolve_static(Contact::class, 'query')
                ->whereNotNull('contact_origin_id')
                ->join('contact_origins', 'contact_origins.id', '=', 'contacts.contact_origin_id')
                ->where('contact_origins.is_active', true)
                ->orderByRaw('COUNT(*) DESC')
        )
            ->setRange($this->timeFrame)
            ->setEndingDate($this->end)
            ->setStartingDate($this->start)
            ->setLabelKey('contactOrigin.name')
            ->count('contact_origin_id');

        if ($this->timeFrame && $range = $this->timeFrame->getRange()) {
            $startDate = $range[0];
            $endDate = $range[1];
        } else {
            $startDate = $this->start;
            $endDate = $this->end;
        }

        $unassignedCount = resolve_static(Contact::class, 'query')
            ->whereNull('contact_origin_id')
            ->where('contacts.created_at', '>=', $startDate)
            ->where('contacts.created_at', '<=', $endDate)
            ->count();

        $this->series = $assignedMetrics->getData();
        $this->labels = $assignedMetrics->getLabels();

        if ($unassignedCount > 0) {
            $this->series[] = $unassignedCount;
            $this->labels[] = __('Unassigned Contacts');
        }
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
        return collect($this->labels)
            ->map(fn ($label) => [
                'label' => $label,
                'method' => 'show',
                'params' => $label,
            ])
            ->toArray();
    }

    #[Renderless]
    public function show(string $contactLabel): void
    {
        $start = $this->getStart()->toDateString();
        $end = $this->getEnd()->toDateString();

        $isUnassigned = $contactLabel === __('Unassigned Contacts');

        SessionFilter::make(
            Livewire::new(resolve_static(ContactList::class, 'class'))->getCacheKey(),
            fn (Builder $query) => $query
                ->whereBetween('contacts.created_at', [$start, $end])
                ->when(! $isUnassigned, function ($q) use ($contactLabel) {
                    return $q->whereNotNull('contact_origin_id')
                        ->join('contact_origins', 'contact_origins.id', '=', 'contacts.contact_origin_id')
                        ->where('contact_origins.is_active', true)
                        ->where('contact_origins.name', $contactLabel);
                })
                ->when($isUnassigned, fn ($q) => $q->whereNull('contacts.contact_origin_id')),
            __('Contacts by :contact-origin', ['contact-origin' => $contactLabel]),
        )->store();

        $this->redirectRoute('contacts.contacts', navigate: true);
    }

    public function showTitle(): bool
    {
        return true;
    }
}
