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

    protected $contactOriginIds = [];

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
                ->whereHas('contactOrigin', function ($query): void {
                    $query->where('is_active', true);
                })
                ->with('contactOrigin:id,name,is_active')
                ->orderByRaw('COUNT(*) DESC')
        )
            ->setRange($this->timeFrame)
            ->setEndingDate($this->getEnd())
            ->setStartingDate($this->getStart())
            ->additionalColumns(['contactOrigin.id'])
            ->setLabelKey('contactOrigin.name')
            ->count('contact_origin_id');

        $startDate = $this->getStart();
        $endDate = $this->getEnd();

        $this->contactOriginIds = $assignedMetrics->getAdditionalData()[0];

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
            ->map(fn (string $label, int $index) => [
                'label' => $label,
                'method' => 'show',
                'params' => [$label, $this->contactOriginIds[$index] ?? null],
            ])
            ->toArray();
    }

    #[Renderless]
    public function show(array $params): void
    {
        $startCarbon = $this->getStart();
        $endCarbon = $this->getEnd();

        $localizedStart = $startCarbon->translatedFormat('j. F Y');
        $localizedEnd = $endCarbon->translatedFormat('j. F Y');

        $contactLabel = $params[0];
        $contactOriginId = $params[1] ?? null;

        $isUnassigned = $contactLabel === __('Unassigned Contacts');

        SessionFilter::make(
            Livewire::new(resolve_static(ContactList::class, 'class'))->getCacheKey(),
            fn (Builder $query) => $query
                ->whereBetween('created_at', [$startCarbon, $endCarbon])
                ->when(! $isUnassigned, function (Builder $query) use ($contactOriginId) {
                    return $query->where('contact_origin_id', $contactOriginId);
                })
                ->when($isUnassigned, fn ($q) => $q->whereNull('contact_origin_id')),
            __('Contacts by :contact-origin', ['contact-origin' => $contactLabel]) . ' ' .
            __('between :start and :end', ['start' => $localizedStart, 'end' => $localizedEnd]),
        )
            ->store();

        $this->redirectRoute('contacts.contacts', navigate: true);
    }

    public function showTitle(): bool
    {
        return true;
    }
}
