<?php

use FluxErp\Livewire\Support\Widgets\ValueBox;
use FluxErp\Livewire\Support\Widgets\ValueList;

class GrowthRateLocaleValueBox extends ValueBox
{
    public static function dashboardComponent(): array|string
    {
        return FluxErp\Livewire\Dashboard\Dashboard::class;
    }

    public function calculateSum(): void
    {
        $this->sum = '1.000,00 €';
        $this->growthRate = '-83.90';
    }
}

class GrowthRateLocaleValueList extends ValueList
{
    public static function dashboardComponent(): array|string
    {
        return FluxErp\Livewire\Dashboard\Dashboard::class;
    }

    public function calculateList(): void
    {
        $this->items = [
            [
                'label' => 'Product',
                'value' => '10',
                'growthRate' => '12.50',
            ],
        ];
    }
}

beforeEach(function (): void {
    $language = FluxErp\Models\Language::query()->firstOrCreate(
        ['language_code' => 'de'],
        FluxErp\Models\Language::factory()->make(['language_code' => 'de'])->toArray()
    );
    $this->user->update(['language_id' => $language->getKey()]);
});

test('value box formats the growth rate with the user locale', function (): void {
    Illuminate\Support\Facades\Route::middleware('web')->get($uri = '/growth-rate-locale/' . uniqid(), GrowthRateLocaleValueBox::class);

    visit($uri)
        ->assertNoSmoke()
        ->assertSee('-83,90 %')
        ->assertDontSee('-83.90%');
});

test('value list formats the growth rate with the user locale', function (): void {
    Illuminate\Support\Facades\Route::middleware('web')->get($uri = '/growth-rate-locale/' . uniqid(), GrowthRateLocaleValueList::class);

    visit($uri)
        ->assertNoSmoke()
        ->assertSee('12,50 %')
        ->assertDontSee('12.50%');
});
