<?php

use FluxErp\Helpers\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder as SpatieQueryBuilder;

it('v7 allowedFields rejects array and accepts spread', function (): void {
    $qb = SpatieQueryBuilder::for(FluxErp\Models\User::class);

    expect(fn () => $qb->allowedFields(['id', 'name']))->toThrow(TypeError::class);

    $result = SpatieQueryBuilder::for(FluxErp\Models\User::class)->allowedFields(...['id', 'name']);
    expect($result)->toBeInstanceOf(SpatieQueryBuilder::class);
});

it('v7 allowedIncludes rejects array and accepts spread', function (): void {
    expect(fn () => SpatieQueryBuilder::for(FluxErp\Models\User::class)->allowedIncludes(['media']))
        ->toThrow(TypeError::class);

    $result = SpatieQueryBuilder::for(FluxErp\Models\User::class)->allowedIncludes(...['media']);
    expect($result)->toBeInstanceOf(SpatieQueryBuilder::class);
});

it('v7 allowedFilters rejects array and accepts spread', function (): void {
    $filters = [AllowedFilter::exact('id'), AllowedFilter::partial('name')];

    expect(fn () => SpatieQueryBuilder::for(FluxErp\Models\User::class)->allowedFilters($filters))
        ->toThrow(TypeError::class);

    $result = SpatieQueryBuilder::for(FluxErp\Models\User::class)->allowedFilters(...$filters);
    expect($result)->toBeInstanceOf(SpatieQueryBuilder::class);
});

it('v7 allowedSorts rejects array and accepts spread', function (): void {
    expect(fn () => SpatieQueryBuilder::for(FluxErp\Models\User::class)->allowedSorts(['id', 'name']))
        ->toThrow(TypeError::class);

    $result = SpatieQueryBuilder::for(FluxErp\Models\User::class)->allowedSorts(...['id', 'name']);
    expect($result)->toBeInstanceOf(SpatieQueryBuilder::class);
});

it('QueryBuilder::filterModel works with spatie query builder v7', function (): void {
    $user = new FluxErp\Models\User();
    $request = new Illuminate\Http\Request();

    $result = QueryBuilder::filterModel($user, $request);

    expect($result)->toBeInstanceOf(SpatieQueryBuilder::class);
});
