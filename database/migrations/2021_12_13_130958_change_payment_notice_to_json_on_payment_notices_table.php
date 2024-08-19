<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ChangePaymentNoticeToJsonOnPaymentNoticesTable extends Migration
{
    public function up(): void
    {
        Schema::table('payment_notices', function (Blueprint $table) {
            $table->json('payment_notice')->change();
        });

        $this->migratePaymentNotice();
    }

    public function down(): void
    {
        $this->rollbackPaymentNotice();

        Schema::table('payment_notices', function (Blueprint $table) {
            $table->string('payment_notice')->change();
        });
    }

    private function migratePaymentNotice()
    {
        $paymentNotices = DB::table('payment_notices')->get()->toArray();

        array_walk($paymentNotices, function (&$item) {
            $item->payment_notice = json_encode([config('app.locale') => $item->payment_notice]);
            $item = (array) $item;
        });

        DB::table('payment_notices')->upsert($paymentNotices, ['id']);
    }

    private function rollbackPaymentNotice()
    {
        $paymentNotices = DB::table('payment_notices')->get()->toArray();

        array_walk($paymentNotices, function (&$item) {
            $item->payment_notice = substr(json_decode($item->payment_notice)->{config('app.locale')}, 0, 255);
            $item = (array) $item;
        });

        DB::table('payment_notices')->upsert($paymentNotices, ['id']);
    }
}
