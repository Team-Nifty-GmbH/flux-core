<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class() extends Migration
{
    public function up(): void
    {
        $serialNumberRanges = DB::table('serial_number_ranges')
            ->where('model_type', 'contact')
            ->where('type', 'customer_number')
            ->select([
                'id',
                'current_number',
            ])
            ->orderBy('current_number', 'DESC')
            ->get()
            ->toArray();

        // Keep serial_number_range if highest current_number
        // and set tenant_id = null, unique_key = "contact..customer_number."
        if ($serialNumberRanges) {
            DB::table('serial_number_ranges')
                ->where('id', array_shift($serialNumberRanges)->id)
                ->update([
                    'tenant_id' => null,
                    'unique_key' => 'contact..customer_number.',
                ]);
            DB::table('serial_number_ranges')
                ->whereIntegerInRaw('id', array_column($serialNumberRanges, 'id'))
                ->delete();
        }
    }

    public function down(): void {}
};
