<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\Calendar;
use FluxErp\Models\CalendarEvent;
use Illuminate\Database\Seeder;

class CalendarEventTableSeeder extends Seeder
{
    public function run(): void
    {
        foreach (Calendar::all(['id']) as $calendar) {
            $calendar->calendarEvents()->saveMany(CalendarEvent::factory()->count(10)->make());
        }
    }
}
