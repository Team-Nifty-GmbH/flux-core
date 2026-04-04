<?php

test('project list loads without js errors', function (): void {
    visit(route('projects'))
        ->assertRoute('projects')
        ->assertNoSmoke()
        ->assertPresent('[tall-datatable]')
        ->assertNoJavascriptErrors();
});
