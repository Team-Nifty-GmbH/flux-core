<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCommoncolumnsToPrintDataTable extends Migration
{
    public function up(): void
    {
        Schema::table('print_data', function (Blueprint $table): void {
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();

            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('updated_by')->references('id')->on('users');
        });
    }

    public function down(): void
    {
        Schema::table('print_data', function (Blueprint $table): void {
            $table->dropForeign('print_data_created_by_foreign');
            $table->dropForeign('print_data_updated_by_foreign');

            $table->dropColumn(['created_by', 'updated_by']);
        });
    }
}
