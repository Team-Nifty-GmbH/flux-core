<?php

namespace FluxErp\Tests\Livewire\Settings;

use FluxErp\Livewire\Settings\ClientEdit;
use FluxErp\Models\Country;
use FluxErp\Models\Currency;
use FluxErp\Models\Language;
use FluxErp\Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;

class ClientEditTest extends TestCase
{
    use DatabaseTransactions;

    public function test_renders_successfully()
    {
        $language = Language::factory()->create();
        $currency = Currency::factory()->create();

        Country::factory()->count(2)->create([
            'language_id' => $language->id,
            'currency_id' => $currency->id,
        ]);

        Livewire::test(ClientEdit::class)
            ->assertStatus(200);
    }
}
