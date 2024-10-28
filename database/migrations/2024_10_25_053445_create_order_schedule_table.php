<?php

use FluxErp\Invokable\ProcessSubscriptionOrder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('order_schedule', function (Blueprint $table) {
            $table->id('pivot_id');
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('schedule_id')->constrained()->cascadeOnDelete();
        });

        $this->migrateSchedules();
    }

    public function down(): void
    {
        Schema::dropIfExists('order_schedule');
    }

    protected function migrateSchedules(): void
    {
        $orderSchedules = DB::table('schedules')
            ->whereJsonContainsKey('parameters->orderId')
            ->whereJsonContainsKey('parameters->orderTypeId')
            ->where('class', ProcessSubscriptionOrder::class)
            ->get(['id', 'parameters']);

        foreach ($orderSchedules as $orderSchedule) {
            $orderId = data_get(json_decode($orderSchedule->parameters, true), 'orderId');

            if ($orderId) {
                DB::table('order_schedule')->insert([
                    'order_id' => $orderId,
                    'schedule_id' => $orderSchedule->id,
                ]);
            }
        }
    }
};
