<?php

use Illuminate\Support\Facades\Schema;

it('has is_inherited boolean column on prices table', function (): void {
    expect(Schema::hasColumn('prices', 'is_inherited'))->toBeTrue();
});

it('has is_inherited boolean column on categorizable table', function (): void {
    expect(Schema::hasColumn('categorizable', 'is_inherited'))->toBeTrue();
});

it('has is_inherited boolean column on product_supplier table', function (): void {
    expect(Schema::hasColumn('product_supplier', 'is_inherited'))->toBeTrue();
});

it('has is_inherited boolean column on product_product_property table', function (): void {
    expect(Schema::hasColumn('product_product_property', 'is_inherited'))->toBeTrue();
});
