<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

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
    }

    public function down(): void {}
};
