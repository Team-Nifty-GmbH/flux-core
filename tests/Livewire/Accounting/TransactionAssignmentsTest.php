<?php

namespace FluxErp\Tests\Livewire\Accounting;

use FluxErp\Livewire\Accounting\TransactionAssignments;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class TransactionAssignmentsTest extends TestCase
{
    protected string $livewireComponent = TransactionAssignments::class;

    public function test_renders_successfully(): void
    {
        Livewire::test($this->livewireComponent)
            ->assertStatus(200);
    }
}
