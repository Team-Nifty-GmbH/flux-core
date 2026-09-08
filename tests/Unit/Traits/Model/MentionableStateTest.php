<?php

use FluxErp\Models\User;

test('returns null state by default for a mentionable without a state', function (): void {
    expect(User::factory()->create()->getMentionState())->toBeNull();
});
