<?php

use Symfony\Component\Finder\Finder;

test('no view calls a modal magic that alpine does not provide', function (): void {
    $offenders = [];

    foreach (Finder::create()->files()->in(__DIR__ . '/../../../resources/views')->name('*.blade.php') as $file) {
        if (preg_match('/\$modal(Open|Close)\s*\(/', $file->getContents())) {
            $offenders[] = $file->getRelativePathname();
        }
    }

    expect($offenders)->toBe([]);
});
