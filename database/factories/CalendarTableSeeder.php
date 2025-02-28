<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\Calendar;
use FluxErp\Traits\HasCalendars;
use Illuminate\Database\Seeder;

class CalendarTableSeeder extends Seeder
{
    public function run(): void
    {
        Calendar::factory()->count(5)->create([
            'is_public' => true,
        ]);

        $calendarables = collect(config('auth.providers'))
            ->pluck('model')
            ->filter(fn ($model) => class_uses_recursive($model)[HasCalendars::class] ?? false);

        foreach ($calendarables as $calendarable) {
            foreach ($calendarable::all(['id']) as $model) {
                $model->calendars()->saveMany(Calendar::factory(['is_public' => false])->count(3)->make());
            }
        }
    }
}
