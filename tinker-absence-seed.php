<?php

use FluxErp\Models\User;
use FluxErp\Models\AbsenceType;
use FluxErp\Models\AbsenceRequest;
use Illuminate\Support\Carbon;

$vacation = AbsenceType::where('name', 'Urlaub')->first();
$sick = AbsenceType::where('name', 'Krankheit')->first();
$homeoffice = AbsenceType::where('name', 'Homeoffice')->first();
$training = AbsenceType::where('name', 'Fortbildung')->first();

$users = User::whereNotNull('employment_date')->get();
$startOfMonth = Carbon::now()->startOfMonth();
$endOfMonth = Carbon::now()->endOfMonth();

echo "Creating absence requests for " . Carbon::now()->format('F Y') . "...\n";

foreach ($users->take(8) as $user) {
    $numAbsences = rand(1, 2);
    
    for ($i = 0; $i < $numAbsences; $i++) {
        $absenceType = collect([$vacation, $sick, $homeoffice, $training])->filter()->random();
        $startDate = $startOfMonth->copy()->addDays(rand(5, 20));
        $duration = $absenceType->name === 'Homeoffice' ? 0 : rand(0, 3);
        $endDate = $startDate->copy()->addDays($duration);
        
        if ($endDate->gt($endOfMonth)) {
            $endDate = $endOfMonth->copy();
        }
        
        $hasOverlap = AbsenceRequest::where('user_id', $user->id)
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate, $endDate])
                    ->orWhereBetween('end_date', [$startDate, $endDate]);
            })
            ->exists();
        
        if (!$hasOverlap) {
            AbsenceRequest::create([
                'user_id' => $user->id,
                'absence_type_id' => $absenceType->id,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'start_half_day' => 'full',
                'end_half_day' => 'full',
                'days_requested' => $startDate->diffInWeekdays($endDate) + 1,
                'reason' => $absenceType->requires_reason ? 'Grund für Abwesenheit' : null,
                'status' => 'approved',
                'approved_by' => $user->supervisor_id,
                'approved_at' => $startDate->copy()->subDays(rand(1, 3)),
                'is_emergency' => false,
                'client_id' => 1,
            ]);
            echo "Created {$absenceType->name} for {$user->name} from {$startDate->format('d.m')} to {$endDate->format('d.m')}\n";
        }
    }
}

echo "\nAbsence requests created!\n";
echo "Visit http://laravel.test/attendance-overview to see the results.\n";