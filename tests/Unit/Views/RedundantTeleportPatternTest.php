<?php

use Symfony\Component\Finder\Finder;

/*
 * Since TallStackUI v3.2.2 every overlay component (modal/slide/sidebar)
 * teleports its own root to <body> internally to escape ancestor
 * containing blocks. Wrapping such an overlay in an outer @teleport('body')
 * therefore creates a *nested* x-teleport. Alpine clones the inner template
 * for each level of nesting, and Livewire then hydrates every cloned
 * subtree as a separate component instance.
 *
 * The visible symptom is that any <livewire:...> component placed inside
 * the modal slot ends up rendered multiple times stacked on top of each
 * other in the DOM (see calendar new-event modal, ticket #1465).
 *
 * The composer constraint pins `tallstackui/tallstackui` to >= 3.2.2, so
 * the redundant outer @teleport may never be reintroduced anywhere.
 */
test('no blade view wraps a TallStackUI overlay in @teleport', function (): void {
    $offenders = [];

    foreach ((new Finder())->files()->in(__DIR__ . '/../../../resources/views')->name('*.blade.php') as $file) {
        $contents = $file->getContents();

        if (! str_contains($contents, '@teleport(')) {
            continue;
        }

        if (preg_match(
            '/@teleport\([^)]*\)\s*(?:\{\{--.*?--\}\}\s*)*<x-(?:tall::)?(?:modal|slide)\b/s',
            $contents
        )) {
            $offenders[] = str_replace(realpath(__DIR__ . '/../../../') . '/', '', $file->getRealPath());
        }
    }

    expect($offenders)->toBe([], sprintf(
        'TallStackUI overlays already teleport to body since v3.2.2. '
        . 'The following blade files still wrap them in a redundant outer @teleport, '
        . "which causes nested teleports and multiplies any Livewire component inside:\n  - %s",
        implode("\n  - ", $offenders)
    ));
});
