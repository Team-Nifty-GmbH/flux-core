<?php

use FluxErp\Models\Contact;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::rename('contact_origins', 'record_origins');

        Schema::table('record_origins', function (Blueprint $table): void {
            $table->string('model_type')->after('id')->index()->nullable();
        });

        $this->migrateModelType();

        Schema::table('record_origins', function (Blueprint $table): void {
            $table->string('model_type')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('record_origins', function (Blueprint $table): void {
            $table->dropColumn('model_type');
        });

        Schema::rename('record_origins', 'contact_origins');
    }

    private function migrateModelType(): void
    {
        DB::transaction(function (): void {
            DB::table('record_origins')
                ->update([
                    'model_type' => morph_alias(Contact::class),
                ]);
        });
    }
};
