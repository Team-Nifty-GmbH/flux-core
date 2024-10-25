<?php

use FluxErp\Invokable\ProcessSubscriptionOrder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('schedule_id')
                ->after('price_list_id')
                ->nullable()
                ->constrained('schedules')
                ->nullOnDelete();
        });

        $this->migrateSchedules();
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('schedule_id');
        });
    }

    protected function migrateSchedules(): void
    {
        $orderSchedules = DB::table('schedules')
            ->whereJsonContainsKey('parameters->orderId')
            ->whereJsonContainsKey('parameters->orderTypeId')
            ->where('class', ProcessSubscriptionOrder::class)
            ->get();

        foreach ($orderSchedules as $orderSchedule) {
            $orderId = data_get(json_decode($orderSchedule->parameters, true), 'orderId');

            if ($orderId) {
                DB::table('orders')
                    ->where('id', $orderId)
                    ->update(['schedule_id' => $orderSchedule->id]);
            }
        }
    }
};
