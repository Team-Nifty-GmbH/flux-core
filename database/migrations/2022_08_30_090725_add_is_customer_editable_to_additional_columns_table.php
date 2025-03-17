<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('additional_columns', function (Blueprint $table): void {
            $table->boolean('is_customer_editable')
                ->default(false)
                ->after('values')
                ->comment('If set to true the customer can edit this field in the customer portal.');
            $table->string('label')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('additional_columns', function (Blueprint $table): void {
            $table->dropColumn('is_customer_editable');
            $table->json('label')->nullable()->change();
        });
    }
};
