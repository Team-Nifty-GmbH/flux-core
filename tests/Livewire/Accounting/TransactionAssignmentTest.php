<?php

namespace FluxErp\Feature\Livewire\Accounting;

use FluxErp\Livewire\Accounting\TransactionAssignment;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class TransactionAssignmentTest extends TestCase
{
    protected string $livewireComponent = TransactionAssignment::class;

    public function test_renders_successfully(): void
    {
        Livewire::test($this->livewireComponent)
            ->assertStatus(200);
    }
}
