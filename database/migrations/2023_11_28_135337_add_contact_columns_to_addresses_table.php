<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('addresses', function (Blueprint $table) {
            $table->string('email')->after('url')->nullable();
            $table->string('phone')->after('email')->nullable();

            $table->string('mailbox_city')->after('mailbox')->nullable();
            $table->string('mailbox_zip')->after('mailbox_city')->nullable();
        });

        DB::statement("UPDATE addresses SET email = (
                SELECT value FROM contact_options
                             WHERE contact_options.address_id = addresses.id AND contact_options.type = 'email'
                             ORDER BY is_primary desc
                             LIMIT 1
            )"
        );

        DB::statement("UPDATE addresses SET phone = (
                SELECT value FROM contact_options
                             WHERE contact_options.address_id = addresses.id AND contact_options.type = 'phone'
                             ORDER BY is_primary desc
                             LIMIT 1
            )"
        );

        DB::statement("UPDATE addresses SET url = (
                SELECT value FROM contact_options
                             WHERE contact_options.address_id = addresses.id AND contact_options.type = 'website'
                             ORDER BY contact_options.is_primary desc
                             LIMIT 1
            )"
        );

        DB::statement('DELETE FROM contact_options WHERE value IN (
                SELECT email FROM addresses WHERE email IS NOT NULL AND addresses.id = contact_options.address_id
                UNION
                SELECT phone FROM addresses WHERE phone IS NOT NULL AND addresses.id = contact_options.address_id
                UNION
                SELECT url FROM addresses WHERE url IS NOT NULL AND addresses.id = contact_options.address_id
            )'
        );
    }

    public function down(): void
    {
        DB::statement("INSERT INTO contact_options (address_id, type, value, is_primary, label)
            SELECT id, 'email', email, 1, 'E-Mail' FROM addresses WHERE email IS NOT NULL
            UNION
            SELECT id, 'phone', phone, 1, 'Telefon' FROM addresses WHERE phone IS NOT NULL
            UNION
            SELECT id, 'website', url, 1, 'Webseite' FROM addresses WHERE url IS NOT NULL
        ");

        Schema::table('addresses', function (Blueprint $table) {
            $table->dropColumn([
                'email',
                'phone',
                'mailbox_city',
                'mailbox_zip',
            ]);
        });
    }
};
