<?php

use Symfony\Component\Finder\Finder;

test('no source file writes a tailwind variant with a leading important marker', function (): void {
    $offenders = [];

    foreach (Finder::create()->files()->in([__DIR__ . '/../../../src', __DIR__ . '/../../../resources'])
        ->name(['*.php', '*.blade.php', '*.js', '*.css']) as $file) {
        foreach (preg_split('/\R/', $file->getContents()) as $number => $line) {
            // Tailwind v4 takes the important marker as a suffix. Written in front of
            // a variant the class is no candidate at all, so no rule is emitted and
            // the utility silently does nothing.
            if (preg_match('/![a-z0-9-]+:[a-z0-9-]+/', $line)) {
                $offenders[] = $file->getRelativePathname() . ':' . ($number + 1);
            }
        }
    }

    expect($offenders)->toBeEmpty();
});
