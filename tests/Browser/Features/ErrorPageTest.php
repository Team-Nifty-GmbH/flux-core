<?php

test('error pages follow the dark theme', function (): void {
    $page = visit('/');
    $page->script("() => localStorage.setItem('dark-theme', 'true')");

    $page->navigate('/' . uniqid('missing-'))
        ->assertSee('404')
        ->assertScript('getComputedStyle(document.body).backgroundColor', 'rgb(30, 41, 59)');
});

test('error pages stay light without the dark theme', function (): void {
    $page = visit('/');
    $page->script("() => localStorage.setItem('dark-theme', 'false')");

    $page->navigate('/' . uniqid('missing-'))
        ->assertSee('404')
        ->assertScript('getComputedStyle(document.body).backgroundColor', 'rgb(248, 250, 252)');
});
