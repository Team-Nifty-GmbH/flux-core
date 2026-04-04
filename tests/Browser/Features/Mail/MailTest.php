<?php

test('mail page loads without js errors', function (): void {
    visit(route('mail'))
        ->assertNoSmoke()
        ->assertNoJavascriptErrors();
});
