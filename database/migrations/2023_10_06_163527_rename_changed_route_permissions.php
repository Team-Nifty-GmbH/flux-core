<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Updated web route permissions
        DB::table('permissions')
            ->where('name', 'my-profile.{id?}.get')
            ->where('guard_name', 'web')
            ->update([
                'name' => 'my-profile.get',
            ]);

        DB::table('permissions')
            ->where('name', 'products.{id?}.get')
            ->where('guard_name', 'web')
            ->update([
                'name' => 'products.{id}.get',
            ]);

        // Updated portal route permissions
        DB::table('permissions')
            ->where('name', 'my-profile.{id?}.get')
            ->where('guard_name', 'address')
            ->update([
                'name' => 'my-profile.get',
            ]);

        DB::table('permissions')
            ->where('name', 'profile.{id?}.get')
            ->where('guard_name', 'address')
            ->update([
                'name' => 'profiles.{id?}.get',
            ]);

        app()->make(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Rollback web route permissions
        DB::table('permissions')
            ->where('name', 'my-profile.get')
            ->where('guard_name', 'web')
            ->update([
                'name' => 'my-profile.{id?}.get',
            ]);

        DB::table('permissions')
            ->where('name', 'products.{id}.get')
            ->where('guard_name', 'web')
            ->update([
                'name' => 'products.{id?}.get',
            ]);

        // Rollback portal route permissions
        DB::table('permissions')
            ->where('name', 'my-profile.get')
            ->where('guard_name', 'address')
            ->update([
                'name' => 'my-profile.{id?}.get',
            ]);

        DB::table('permissions')
            ->where('name', 'profiles.{id?}.get')
            ->where('guard_name', 'address')
            ->update([
                'name' => 'profile.{id?}.get',
            ]);

        app()->make(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
