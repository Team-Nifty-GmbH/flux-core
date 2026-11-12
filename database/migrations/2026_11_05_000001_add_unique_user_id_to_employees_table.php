<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        DB::table('employees')
            ->whereNotNull('deleted_at')
            ->whereNotNull('user_id')
            ->update(['user_id' => null]);

        DB::table('employees')
            ->whereNotNull('user_id')
            ->whereNotIn(
                'id',
                DB::query()->fromSub(
                    DB::table('employees')
                        ->whereNotNull('user_id')
                        ->selectRaw('MIN(id) AS id')
                        ->groupBy('user_id'),
                    'first_employees'
                )
                    ->select('id')
            )
            ->update(['user_id' => null]);

        Schema::table('employees', function (Blueprint $table): void {
            $table->unique('user_id');
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table): void {
            $table->dropUnique(['user_id']);
        });
    }
};
