<?php

namespace FluxErp\Livewire\HumanResources;

use FluxErp\Livewire\Features\Calendar\Calendar as BaseCalendar;
use FluxErp\Models\Holiday;
use FluxErp\Models\AbsenceRequest;
use FluxErp\Models\AbsenceType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Livewire\Attributes\On;
use Livewire\Attributes\Renderless;

class Calendar extends BaseCalendar
{
    public ?int $userId = null;
    public bool $showHolidays = true;
    public array $selectedAbsenceTypes = [];

    #[Renderless]
    #[On('calendar-event-click')]
    #[On('calendar-event-change')]
    public function editEvent(array $event, ?string $trigger = null): void
    {
        if (data_get($event, 'extendedProps.type') === 'holiday') {
            return;
        }
        
        if (data_get($event, 'extendedProps.type') === 'absence') {
            $absenceRequest = resolve_static(AbsenceRequest::class, 'find', ['id' => data_get($event, 'extendedProps.absence_id')]);
            if ($absenceRequest) {
                $this->dispatch('edit-absence-request', id: $absenceRequest->id);
            }
            return;
        }
        
        parent::editEvent($event, $trigger);
    }

    #[Renderless]
    public function getCalendars(): array
    {
        $calendars = [];
        
        // Holiday calendar
        if ($this->showHolidays) {
            $calendars[] = [
                'id' => 'holidays',
                'name' => __('Holidays'),
                'label' => __('Holidays'),
                'hasNoEvents' => false,
                'color' => '#dc2626',
                'is_editable' => false,
                'children' => [],
            ];
        }
        
        // Load all absence types and create a calendar for each
        $absenceTypes = resolve_static(AbsenceType::class, 'query')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
        
        // Generate colors for absence types
        $colors = [
            '#3b82f6', // blue
            '#10b981', // emerald
            '#f59e0b', // amber
            '#8b5cf6', // violet
            '#ec4899', // pink
            '#06b6d4', // cyan
            '#84cc16', // lime
            '#f97316', // orange
            '#6366f1', // indigo
            '#14b8a6', // teal
        ];
        
        foreach ($absenceTypes as $index => $absenceType) {
            $colorIndex = $index % count($colors);
            $calendars[] = [
                'id' => 'absence-type-' . $absenceType->id,
                'name' => $absenceType->name,
                'label' => $absenceType->name,
                'hasNoEvents' => false,
                'color' => $absenceType->color ?? $colors[$colorIndex],
                'is_editable' => false,
                'children' => [],
                'extendedProps' => [
                    'absence_type_id' => $absenceType->id,
                ],
            ];
            
            // Initialize selected types on first load
            if (empty($this->selectedAbsenceTypes)) {
                $this->selectedAbsenceTypes[] = 'absence-type-' . $absenceType->id;
            }
        }
        
        return $calendars;
    }
    
    #[Renderless]
    public function getEvents(array $info, array $calendarAttributes): array
    {
        $events = [];
        $start = Carbon::parse($info['start']);
        $end = Carbon::parse($info['end']);
        $calendarId = data_get($calendarAttributes, 'id');
        
        // Handle holidays calendar
        if ($calendarId === 'holidays' && $this->showHolidays) {
            $holidays = resolve_static(Holiday::class, 'query')
                ->where('is_active', true)
                ->where(function ($query) use ($start, $end) {
                    $query->whereBetween('date', [$start, $end])
                        ->orWhere(function ($q) use ($start, $end) {
                            $q->whereNull('date')
                                ->where('is_recurring', true)
                                ->whereRaw('MONTH(CONCAT(YEAR(NOW()), "-", LPAD(month, 2, "0"), "-", LPAD(day, 2, "0"))) BETWEEN ? AND ?', [$start->month, $end->month]);
                        });
                })
                ->get();
                
            foreach ($holidays as $holiday) {
                $holidayDate = $holiday->date ?: Carbon::createFromFormat('Y-m-d', $start->year . '-' . str_pad($holiday->month, 2, '0', STR_PAD_LEFT) . '-' . str_pad($holiday->day, 2, '0', STR_PAD_LEFT));
                
                $events[] = [
                    'id' => 'holiday-' . $holiday->id,
                    'title' => $holiday->name,
                    'start' => $holidayDate->format('Y-m-d'),
                    'allDay' => true,
                    'color' => '#dc2626',
                    'extendedProps' => [
                        'type' => 'holiday',
                        'holiday_id' => $holiday->id,
                        'location' => $holiday->location?->name,
                    ],
                ];
            }
        }
        
        // Handle absence type calendars
        if (str_starts_with($calendarId, 'absence-type-')) {
            $absenceTypeId = str_replace('absence-type-', '', $calendarId);
            $calendarColor = data_get($calendarAttributes, 'color', '#3b82f6');
            
            $absenceRequests = resolve_static(AbsenceRequest::class, 'query')
                ->where('absence_type_id', $absenceTypeId)
                ->whereIn('status', ['pending', 'approved'])
                ->where(function ($query) use ($start, $end) {
                    $query->whereBetween('start_date', [$start, $end])
                        ->orWhereBetween('end_date', [$start, $end])
                        ->orWhere(function ($q) use ($start, $end) {
                            $q->where('start_date', '<=', $start)
                                ->where('end_date', '>=', $end);
                        });
                })
                ->with(['user', 'absenceType'])
                ->get();
                
            foreach ($absenceRequests as $request) {
                $title = $request->user->name;
                
                // Add status indicator for pending requests
                if ($request->status === 'pending') {
                    $title .= ' (' . __('Pending') . ')';
                }
                
                $events[] = [
                    'id' => 'absence-' . $request->id,
                    'title' => $title,
                    'start' => $request->start_date->format('Y-m-d'),
                    'end' => $request->end_date->addDay()->format('Y-m-d'),
                    'color' => $request->status === 'approved' ? $calendarColor : '#94a3b8',
                    'extendedProps' => [
                        'type' => 'absence',
                        'absence_id' => $request->id,
                        'absence_type' => $request->absenceType->name,
                        'user' => $request->user->name,
                        'status' => $request->status,
                        'days' => $request->days_requested,
                    ],
                ];
            }
        }
        
        return $events;
    }
}