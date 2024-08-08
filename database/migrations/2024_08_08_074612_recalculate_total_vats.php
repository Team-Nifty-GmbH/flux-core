<?php

use Illuminate\Database\Migrations\Migration;

return new class() extends Migration
{
    public function up(): void
    {
        morphed_model('order')::query()
            ->whereNotNull('total_vats')
            ->each(function ($order) {
                $order->calculateTotalVats()->save();
            });
    }

    public function down(): void
    {
        //
    }
};
