<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\Calendar;
use FluxErp\Models\CalendarEvent;
use Illuminate\Database\Seeder;

class CalendarEventTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach (Calendar::all() as $calendar) {
            $calendar->calendarEvents()->saveMany(CalendarEvent::factory()->count(10)->make());
        }
    }
}
