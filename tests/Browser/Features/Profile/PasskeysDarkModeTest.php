<?php

test('passkey form follows the dark theme', function (): void {
    $page = visit(route('my-profile'));
    $page->script("() => localStorage.setItem('dark-theme', 'true')");

    $page->navigate(route('my-profile'))
        ->assertScript(
            "getComputedStyle(document.querySelector('#passkeyForm input')).backgroundColor !== 'rgb(255, 255, 255)'",
            true
        );
});
