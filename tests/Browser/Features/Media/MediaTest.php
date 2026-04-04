<?php

test('media page loads without js errors', function (): void {
    visit(route('media-grid'))
        ->assertNoSmoke()
        ->assertNoJavascriptErrors();
});

test('media page renders without js errors', function (): void {
    visit(route('media-grid'))
        ->assertNoSmoke()
        ->assertNoJavascriptErrors();
});
