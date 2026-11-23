<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Str;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

test('every model has a morph alias', function (): void {
    $models = collect(
        Finder::create()
            ->files()
            ->in(flux_path('src/Models'))
            ->name('*.php')
    )
        ->map(
            fn (SplFileInfo $file): string => 'FluxErp\\Models\\'
                . str_replace('/', '\\', Str::beforeLast($file->getRelativePathname(), '.php'))
        )
        ->filter(
            fn (string $class): bool => is_subclass_of($class, Model::class)
                && ! (new ReflectionClass($class))->isAbstract()
        )
        ->values();

    expect($models)->not->toBeEmpty()
        ->and($models->diff(Relation::morphMap())->values()->all())->toBe([]);
});
