<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('work_times', function (Blueprint $table) {
            $table->foreign(['contact_id'])->references(['id'])->on('contacts')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['order_position_id'])->references(['id'])->on('order_positions')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['parent_id'])->references(['id'])->on('work_times')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['user_id'])->references(['id'])->on('users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['work_time_type_id'])->references(['id'])->on('work_time_types')->onUpdate('no action')->onDelete('no action');
        });
    }

    public function down(): void
    {
        Schema::table('work_times', function (Blueprint $table) {
            $table->dropForeign('work_times_contact_id_foreign');
            $table->dropForeign('work_times_order_position_id_foreign');
            $table->dropForeign('work_times_parent_id_foreign');
            $table->dropForeign('work_times_user_id_foreign');
            $table->dropForeign('work_times_work_time_type_id_foreign');
        });
    }
};
