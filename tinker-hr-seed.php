<?php

use FluxErp\Models\User;
use FluxErp\Models\WorkTimeModel;
use FluxErp\Models\Location;
use FluxErp\Models\AbsenceType;
use FluxErp\Models\AbsenceRequest;
use Illuminate\Support\Carbon;

// Hole oder erstelle Arbeitszeitmodelle
$fullTime = WorkTimeModel::firstOrCreate(
    ['name' => 'Vollzeit'],
    [
        'cycle_weeks' => 1,
        'weekly_hours' => 40,
        'annual_vacation_days' => 30,
        'max_overtime_hours' => 200,
        'overtime_compensation' => 'time_off',
        'is_active' => true,
        'client_id' => 1,
    ]
);

$partTime = WorkTimeModel::firstOrCreate(
    ['name' => 'Teilzeit 80%'],
    [
        'cycle_weeks' => 1,
        'weekly_hours' => 32,
        'annual_vacation_days' => 24,
        'max_overtime_hours' => 150,
        'overtime_compensation' => 'time_off',
        'is_active' => true,
        'client_id' => 1,
    ]
);

// Hole oder erstelle Standorte
$berlin = Location::firstOrCreate(
    ['name' => 'Hauptsitz Berlin'],
    [
        'address' => 'Alexanderplatz 1',
        'postal_code' => '10178',
        'city' => 'Berlin',
        'country' => 'DE',
        'phone' => '+49 30 123456',
        'email' => 'berlin@example.com',
        'is_active' => true,
        'client_id' => 1,
    ]
);

$munich = Location::firstOrCreate(
    ['name' => 'Niederlassung München'],
    [
        'address' => 'Marienplatz 1',
        'postal_code' => '80331',
        'city' => 'München',
        'country' => 'DE',
        'phone' => '+49 89 123456',
        'email' => 'muenchen@example.com',
        'is_active' => true,
        'client_id' => 1,
    ]
);

// Hole Abwesenheitstypen
$vacation = AbsenceType::where('name', 'Urlaub')->first();
$sick = AbsenceType::where('name', 'Krankheit')->first();
$homeoffice = AbsenceType::where('name', 'Homeoffice')->first();
$training = AbsenceType::where('name', 'Fortbildung')->first();
$business = AbsenceType::where('name', 'Dienstreise')->first();

// Update alle User mit HR-Daten
$users = User::all();
$supervisors = $users->take(2); // Erste 2 User sind Supervisoren

foreach ($users as $index => $user) {
    // Zufällige HR-Daten zuweisen
    $isFullTime = $index % 3 !== 0; // 2/3 Vollzeit, 1/3 Teilzeit
    $location = $index % 2 === 0 ? $berlin : $munich;
    $supervisor = $supervisors->where('id', '!=', $user->id)->random();
    
    $user->update([
        'work_time_model_id' => $isFullTime ? $fullTime->id : $partTime->id,
        'location_id' => $location->id,
        'supervisor_id' => $user->id <= 2 ? null : $supervisor->id, // Supervisoren haben keinen Supervisor
        'employment_date' => Carbon::now()->subMonths(rand(3, 60)),
        'birth_date' => Carbon::now()->subYears(rand(25, 55))->subDays(rand(0, 365)),
        'salary' => rand(35000, 120000),
        'salary_type' => $isFullTime ? 'yearly' : 'monthly',
        'vacation_days_current' => $isFullTime ? rand(20, 30) : rand(15, 24),
        'vacation_days_carried' => rand(0, 5),
        'overtime_hours' => rand(0, 50) * 0.5,
        'social_security_number' => 'SV' . str_pad($index + 1, 6, '0', STR_PAD_LEFT),
        'tax_id' => 'TAX' . str_pad($index + 1, 8, '0', STR_PAD_LEFT),
        'tax_class' => (string)rand(1, 6),
        'emergency_contact_name' => fake()->name(),
        'emergency_contact_phone' => fake()->phoneNumber(),
        'emergency_contact_relation' => fake()->randomElement(['Ehepartner', 'Eltern', 'Geschwister', 'Partner']),
    ]);
    
    echo "Updated user: {$user->name} with HR data\n";
}

// Erstelle Abwesenheiten für den aktuellen Monat
$currentMonth = Carbon::now();
$startOfMonth = $currentMonth->copy()->startOfMonth();
$endOfMonth = $currentMonth->copy()->endOfMonth();

echo "\nCreating absence requests for current month ({$currentMonth->format('F Y')})...\n";

foreach ($users as $user) {
    // Jeder User hat 0-3 Abwesenheiten im Monat
    $numAbsences = rand(0, 3);
    
    for ($i = 0; $i < $numAbsences; $i++) {
        $absenceType = collect([$vacation, $sick, $homeoffice, $training, $business])->random();
        
        // Zufälliger Start im Monat
        $startDate = $startOfMonth->copy()->addDays(rand(0, 25));
        
        // Dauer: 1-5 Tage für die meisten, 1 Tag für Homeoffice
        $duration = $absenceType->name === 'Homeoffice' ? 0 : rand(0, 4);
        $endDate = $startDate->copy()->addDays($duration);
        
        // Stelle sicher, dass es nicht über den Monat hinausgeht
        if ($endDate->gt($endOfMonth)) {
            $endDate = $endOfMonth->copy();
        }
        
        // Prüfe auf Überschneidungen
        $hasOverlap = AbsenceRequest::where('user_id', $user->id)
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate, $endDate])
                    ->orWhereBetween('end_date', [$startDate, $endDate])
                    ->orWhere(function ($q) use ($startDate, $endDate) {
                        $q->where('start_date', '<=', $startDate)
                            ->where('end_date', '>=', $endDate);
                    });
            })
            ->exists();
        
        if (!$hasOverlap) {
            $daysRequested = $startDate->diffInWeekdays($endDate) + 1;
            
            AbsenceRequest::create([
                'user_id' => $user->id,
                'absence_type_id' => $absenceType->id,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'start_half_day' => 'full',
                'end_half_day' => 'full',
                'days_requested' => $daysRequested,
                'reason' => $absenceType->requires_reason ? fake()->sentence() : null,
                'status' => 'approved',
                'approved_by' => $user->supervisor_id,
                'approved_at' => $startDate->copy()->subDays(rand(1, 7)),
                'is_emergency' => false,
                'client_id' => 1,
            ]);
            
            echo "Created {$absenceType->name} for {$user->name} from {$startDate->format('d.m')} to {$endDate->format('d.m')}\n";
        }
    }
}

echo "\nHR data seeding completed!\n";
echo "You can now visit /attendance-overview to see the attendance overview.\n";