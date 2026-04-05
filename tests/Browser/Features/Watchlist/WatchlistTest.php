<?php

test('watchlist page loads without js errors', function (): void {
    visit(route('watchlists'))
        ->assertNoSmoke();
});
