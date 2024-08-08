<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        // Categories table
        Schema::table('categories', function (Blueprint $table) {
            $table->string('name_migration')->after('name');
        });
        DB::table('categories')->update([
            'name_migration' => DB::raw("COALESCE(NULLIF(JSON_UNQUOTE(JSON_EXTRACT(name, CONCAT('$.', JSON_UNQUOTE(JSON_EXTRACT(JSON_KEYS(name), '$[0]'))))), 'null'), NULL)"),
        ]);
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('name');
            $table->renameColumn('name_migration', 'name');
        });

        // Address Types table
        Schema::table('address_types', function (Blueprint $table) {
            $table->string('name_migration')->after('name');
        });
        DB::table('address_types')->update([
            'name_migration' => DB::raw("COALESCE(NULLIF(JSON_UNQUOTE(JSON_EXTRACT(name, CONCAT('$.', JSON_UNQUOTE(JSON_EXTRACT(JSON_KEYS(name), '$[0]'))))), 'null'), NULL)"),
        ]);
        Schema::table('address_types', function (Blueprint $table) {
            $table->dropColumn('name');
            $table->renameColumn('name_migration', 'name');
        });

        // Order Types table
        Schema::table('order_types', function (Blueprint $table) {
            $table->string('name_migration')->after('name');
            $table->string('description_migration')->nullable()->after('description');
        });
        DB::table('order_types')->update([
            'name_migration' => DB::raw("COALESCE(NULLIF(JSON_UNQUOTE(JSON_EXTRACT(name, CONCAT('$.', JSON_UNQUOTE(JSON_EXTRACT(JSON_KEYS(name), '$[0]'))))), 'null'), NULL)"),
            'description_migration' => DB::raw("COALESCE(NULLIF(JSON_UNQUOTE(JSON_EXTRACT(description, CONCAT('$.', JSON_UNQUOTE(JSON_EXTRACT(JSON_KEYS(description), '$[0]'))))), 'null'), NULL)"),
        ]);
        Schema::table('order_types', function (Blueprint $table) {
            $table->dropColumn('name');
            $table->dropColumn('description');
            $table->renameColumn('name_migration', 'name');
            $table->renameColumn('description_migration', 'description');
        });

        // Payment Notices table
        Schema::table('payment_notices', function (Blueprint $table) {
            $table->string('payment_notice_migration')->nullable()->after('payment_notice');
        });
        DB::table('payment_notices')->update([
            'payment_notice_migration' => DB::raw("COALESCE(NULLIF(JSON_UNQUOTE(JSON_EXTRACT(payment_notice, CONCAT('$.', JSON_UNQUOTE(JSON_EXTRACT(JSON_KEYS(payment_notice), '$[0]'))))), 'null'), NULL)"),
        ]);
        Schema::table('payment_notices', function (Blueprint $table) {
            $table->dropColumn('payment_notice');
            $table->renameColumn('payment_notice_migration', 'payment_notice');
        });

        // Product Option Groups table
        Schema::table('product_option_groups', function (Blueprint $table) {
            $table->string('name_migration')->after('name');
        });
        DB::table('product_option_groups')->update([
            'name_migration' => DB::raw("COALESCE(NULLIF(JSON_UNQUOTE(JSON_EXTRACT(name, CONCAT('$.', JSON_UNQUOTE(JSON_EXTRACT(JSON_KEYS(name), '$[0]'))))), 'null'), NULL)"),
        ]);
        Schema::table('product_option_groups', function (Blueprint $table) {
            $table->dropColumn('name');
            $table->renameColumn('name_migration', 'name');
        });

        // Country Regions table
        Schema::table('country_regions', function (Blueprint $table) {
            $table->string('name_migration')->after('name');
        });
        DB::table('country_regions')->update([
            'name_migration' => DB::raw("COALESCE(NULLIF(JSON_UNQUOTE(JSON_EXTRACT(name, CONCAT('$.', JSON_UNQUOTE(JSON_EXTRACT(JSON_KEYS(name), '$[0]'))))), 'null'), NULL)"),
        ]);
        Schema::table('country_regions', function (Blueprint $table) {
            $table->dropColumn('name');
            $table->renameColumn('name_migration', 'name');
        });

        // Work Time Types table
        Schema::table('work_time_types', function (Blueprint $table) {
            $table->string('name_migration')->after('name');
        });
        DB::table('work_time_types')->update([
            'name_migration' => DB::raw("COALESCE(NULLIF(JSON_UNQUOTE(JSON_EXTRACT(name, CONCAT('$.', JSON_UNQUOTE(JSON_EXTRACT(JSON_KEYS(name), '$[0]'))))), 'null'), NULL)"),
        ]);
        Schema::table('work_time_types', function (Blueprint $table) {
            $table->dropColumn('name');
            $table->renameColumn('name_migration', 'name');
        });

        // Product Properties table
        Schema::table('product_properties', function (Blueprint $table) {
            $table->string('name_migration')->after('name');
        });
        DB::table('product_properties')->update([
            'name_migration' => DB::raw("COALESCE(NULLIF(JSON_UNQUOTE(JSON_EXTRACT(name, CONCAT('$.', JSON_UNQUOTE(JSON_EXTRACT(JSON_KEYS(name), '$[0]'))))), 'null'), NULL)"),
        ]);
        Schema::table('product_properties', function (Blueprint $table) {
            $table->dropColumn('name');
            $table->renameColumn('name_migration', 'name');
        });

        // Ticket Types table
        Schema::table('ticket_types', function (Blueprint $table) {
            $table->string('name_migration')->after('name');
        });
        DB::table('ticket_types')->update([
            'name_migration' => DB::raw("COALESCE(NULLIF(JSON_UNQUOTE(JSON_EXTRACT(name, CONCAT('$.', JSON_UNQUOTE(JSON_EXTRACT(JSON_KEYS(name), '$[0]'))))), 'null'), NULL)"),
        ]);
        Schema::table('ticket_types', function (Blueprint $table) {
            $table->dropColumn('name');
            $table->renameColumn('name_migration', 'name');
        });

        // Payment Types table
        Schema::table('payment_types', function (Blueprint $table) {
            $table->string('name_migration')->after('name');
            $table->string('description_migration')->nullable()->after('description');
        });
        DB::table('payment_types')->update([
            'name_migration' => DB::raw("COALESCE(NULLIF(JSON_UNQUOTE(JSON_EXTRACT(name, CONCAT('$.', JSON_UNQUOTE(JSON_EXTRACT(JSON_KEYS(name), '$[0]'))))), 'null'), NULL)"),
            'description_migration' => DB::raw("COALESCE(NULLIF(JSON_UNQUOTE(JSON_EXTRACT(description, CONCAT('$.', JSON_UNQUOTE(JSON_EXTRACT(JSON_KEYS(description), '$[0]'))))), 'null'), NULL)"),
        ]);
        Schema::table('payment_types', function (Blueprint $table) {
            $table->dropColumn('name');
            $table->dropColumn('description');
            $table->renameColumn('name_migration', 'name');
            $table->renameColumn('description_migration', 'description');
        });

        // Languages table
        Schema::table('languages', function (Blueprint $table) {
            $table->string('name_migration')->after('name');
        });
        DB::table('languages')->update([
            'name_migration' => DB::raw("COALESCE(NULLIF(JSON_UNQUOTE(JSON_EXTRACT(name, CONCAT('$.', JSON_UNQUOTE(JSON_EXTRACT(JSON_KEYS(name), '$[0]'))))), 'null'), NULL)"),
        ]);
        Schema::table('languages', function (Blueprint $table) {
            $table->dropColumn('name');
            $table->renameColumn('name_migration', 'name');
        });

        // Product Options table
        Schema::table('product_options', function (Blueprint $table) {
            $table->string('name_migration')->after('name');
        });
        DB::table('product_options')->update([
            'name_migration' => DB::raw("COALESCE(NULLIF(JSON_UNQUOTE(JSON_EXTRACT(name, CONCAT('$.', JSON_UNQUOTE(JSON_EXTRACT(JSON_KEYS(name), '$[0]'))))), 'null'), NULL)"),
        ]);
        Schema::table('product_options', function (Blueprint $table) {
            $table->dropColumn('name');
            $table->renameColumn('name_migration', 'name');
        });

        // Document Types table
        Schema::table('document_types', function (Blueprint $table) {
            $table->string('name_migration')->after('name');
            $table->string('description_migration')->nullable()->after('description');
            $table->string('additional_header_migration')->nullable()->after('additional_header');
            $table->string('additional_footer_migration')->nullable()->after('additional_footer');
        });
        DB::table('document_types')->update([
            'name_migration' => DB::raw("COALESCE(NULLIF(JSON_UNQUOTE(JSON_EXTRACT(name, CONCAT('$.', JSON_UNQUOTE(JSON_EXTRACT(JSON_KEYS(name), '$[0]'))))), 'null'), NULL)"),
            'description_migration' => DB::raw("COALESCE(NULLIF(JSON_UNQUOTE(JSON_EXTRACT(description, CONCAT('$.', JSON_UNQUOTE(JSON_EXTRACT(JSON_KEYS(description), '$[0]'))))), 'null'), NULL)"),
            'additional_header_migration' => DB::raw("COALESCE(NULLIF(JSON_UNQUOTE(JSON_EXTRACT(additional_header, CONCAT('$.', JSON_UNQUOTE(JSON_EXTRACT(JSON_KEYS(additional_header), '$[0]'))))), 'null'), NULL)"),
            'additional_footer_migration' => DB::raw("COALESCE(NULLIF(JSON_UNQUOTE(JSON_EXTRACT(additional_footer, CONCAT('$.', JSON_UNQUOTE(JSON_EXTRACT(JSON_KEYS(additional_footer), '$[0]'))))), 'null'), NULL)"),
        ]);
        Schema::table('document_types', function (Blueprint $table) {
            $table->dropColumn('name');
            $table->dropColumn('description');
            $table->dropColumn('additional_header');
            $table->dropColumn('additional_footer');
            $table->renameColumn('name_migration', 'name');
            $table->renameColumn('description_migration', 'description');
            $table->renameColumn('additional_header_migration', 'additional_header');
            $table->renameColumn('additional_footer_migration', 'additional_footer');
        });

        // Countries table
        Schema::table('countries', function (Blueprint $table) {
            $table->string('name_migration')->after('name');
        });
        DB::table('countries')->update([
            'name_migration' => DB::raw("COALESCE(NULLIF(JSON_UNQUOTE(JSON_EXTRACT(name, CONCAT('$.', JSON_UNQUOTE(JSON_EXTRACT(JSON_KEYS(name), '$[0]'))))), 'null'), NULL)"),
        ]);
        Schema::table('countries', function (Blueprint $table) {
            $table->dropColumn('name');
            $table->renameColumn('name_migration', 'name');
        });

        // Products table
        Schema::table('products', function (Blueprint $table) {
            $table->string('name_migration')->after('name');
            $table->longText('description_migration')->nullable()->after('description');
        });
        DB::table('products')->update([
            'name_migration' => DB::raw("COALESCE(NULLIF(JSON_UNQUOTE(JSON_EXTRACT(name, CONCAT('$.', JSON_UNQUOTE(JSON_EXTRACT(JSON_KEYS(name), '$[0]'))))), 'null'), NULL)"),
            'description_migration' => DB::raw("COALESCE(NULLIF(JSON_UNQUOTE(JSON_EXTRACT(description, CONCAT('$.', JSON_UNQUOTE(JSON_EXTRACT(JSON_KEYS(description), '$[0]'))))), 'null'), NULL)"),
        ]);
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('name');
            $table->dropColumn('description');
            $table->renameColumn('name_migration', 'name');
            $table->renameColumn('description_migration', 'description');
            $table->string('name')->nullable()->change();
            $table->longText('description')->nullable()->change();
        });

        // Orders table
        Schema::table('orders', function (Blueprint $table) {
            $table->longText('header_migration')->nullable()->after('header');
            $table->longText('footer_migration')->nullable()->after('footer');
            $table->longText('logistic_note_migration')->nullable()->after('logistic_note');
        });
        DB::table('orders')->update([
            'header_migration' => DB::raw("COALESCE(NULLIF(JSON_UNQUOTE(JSON_EXTRACT(header, CONCAT('$.', JSON_UNQUOTE(JSON_EXTRACT(JSON_KEYS(header), '$[0]'))))), 'null'), NULL)"),
            'footer_migration' => DB::raw("COALESCE(NULLIF(JSON_UNQUOTE(JSON_EXTRACT(footer, CONCAT('$.', JSON_UNQUOTE(JSON_EXTRACT(JSON_KEYS(footer), '$[0]'))))), 'null'), NULL)"),
            'logistic_note_migration' => DB::raw("COALESCE(NULLIF(JSON_UNQUOTE(JSON_EXTRACT(logistic_note, CONCAT('$.', JSON_UNQUOTE(JSON_EXTRACT(JSON_KEYS(logistic_note), '$[0]'))))), 'null'), NULL)"),
        ]);
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('header');
            $table->dropColumn('footer');
            $table->dropColumn('logistic_note');
            $table->renameColumn('header_migration', 'header');
            $table->renameColumn('footer_migration', 'footer');
            $table->renameColumn('logistic_note_migration', 'logistic_note');
            $table->longText('header')->nullable()->change();
            $table->longText('footer')->nullable()->change();
            $table->longText('logistic_note')->nullable()->change();
        });
    }

    public function down(): void
    {
        // Ensure the values are valid JSON format before changing column types

        // Categories table
        DB::table('categories')->update([
            'name' => DB::raw("JSON_SET('{}', '$." . app()->getLocale() . "', name)"),
        ]);
        Schema::table('categories', function (Blueprint $table) {
            $table->json('name')->change();
        });

        // Address Types table
        DB::table('address_types')->update([
            'name' => DB::raw("JSON_SET('{}', '$." . app()->getLocale() . "', name)"),
        ]);
        Schema::table('address_types', function (Blueprint $table) {
            $table->json('name')->change();
        });

        // Order Types table
        DB::table('order_types')->update([
            'name' => DB::raw("JSON_SET('{}', '$." . app()->getLocale() . "', name)"),
            'description' => DB::raw("JSON_SET('{}', '$." . app()->getLocale() . "', description)"),
        ]);
        Schema::table('order_types', function (Blueprint $table) {
            $table->json('name')->change();
            $table->json('description')->change();
        });

        // Payment Notices table
        DB::table('payment_notices')->update([
            'payment_notice' => DB::raw("JSON_SET('{}', '$." . app()->getLocale() . "', payment_notice)"),
        ]);
        Schema::table('payment_notices', function (Blueprint $table) {
            $table->json('payment_notice')->nullable()->change();
        });

        // Product Option Groups table
        DB::table('product_option_groups')->update([
            'name' => DB::raw("JSON_SET('{}', '$." . app()->getLocale() . "', name)"),
        ]);
        Schema::table('product_option_groups', function (Blueprint $table) {
            $table->json('name')->change();
        });

        // Country Regions table
        DB::table('country_regions')->update([
            'name' => DB::raw("JSON_SET('{}', '$." . app()->getLocale() . "', name)"),
        ]);
        Schema::table('country_regions', function (Blueprint $table) {
            $table->json('name')->change();
        });

        // Work Time Types table
        DB::table('work_time_types')->update([
            'name' => DB::raw("JSON_SET('{}', '$." . app()->getLocale() . "', name)"),
        ]);
        Schema::table('work_time_types', function (Blueprint $table) {
            $table->json('name')->change();
        });

        // Product Properties table
        DB::table('product_properties')->update([
            'name' => DB::raw("JSON_SET('{}', '$." . app()->getLocale() . "', name)"),
        ]);
        Schema::table('product_properties', function (Blueprint $table) {
            $table->json('name')->change();
        });

        // Ticket Types table
        DB::table('ticket_types')->update([
            'name' => DB::raw("JSON_SET('{}', '$." . app()->getLocale() . "', name)"),
        ]);
        Schema::table('ticket_types', function (Blueprint $table) {
            $table->json('name')->change();
        });

        // Payment Types table
        DB::table('payment_types')->update([
            'name' => DB::raw("JSON_SET('{}', '$." . app()->getLocale() . "', name)"),
            'description' => DB::raw("JSON_SET('{}', '$." . app()->getLocale() . "', description)"),
        ]);
        Schema::table('payment_types', function (Blueprint $table) {
            $table->json('name')->change();
            $table->json('description')->nullable()->change();
        });

        // Languages table
        DB::table('languages')->update([
            'name' => DB::raw("JSON_SET('{}', '$." . app()->getLocale() . "', name)"),
        ]);
        Schema::table('languages', function (Blueprint $table) {
            $table->json('name')->change();
        });

        // Product Options table
        DB::table('product_options')->update([
            'name' => DB::raw("JSON_SET('{}', '$." . app()->getLocale() . "', name)"),
        ]);
        Schema::table('product_options', function (Blueprint $table) {
            $table->json('name')->change();
        });

        // Document Types table
        DB::table('document_types')->update([
            'name' => DB::raw("JSON_SET('{}', '$." . app()->getLocale() . "', name)"),
            'description' => DB::raw("JSON_SET('{}', '$." . app()->getLocale() . "', description)"),
            'additional_header' => DB::raw("JSON_SET('{}', '$." . app()->getLocale() . "', additional_header)"),
            'additional_footer' => DB::raw("JSON_SET('{}', '$." . app()->getLocale() . "', additional_footer)"),
        ]);
        Schema::table('document_types', function (Blueprint $table) {
            $table->json('name')->change();
            $table->json('description')->nullable()->change();
            $table->json('additional_header')->nullable()->change();
            $table->json('additional_footer')->nullable()->change();
        });

        // Countries table
        DB::table('countries')->update([
            'name' => DB::raw("JSON_SET('{}', '$." . app()->getLocale() . "', name)"),
        ]);
        Schema::table('countries', function (Blueprint $table) {
            $table->json('name')->change();
        });

        // Products table
        DB::table('products')->update([
            'name' => DB::raw("JSON_SET('{}', '$." . app()->getLocale() . "', name)"),
            'description' => DB::raw("JSON_SET('{}', '$." . app()->getLocale() . "', description)"),
        ]);
        Schema::table('products', function (Blueprint $table) {
            $table->json('name')->change();
            $table->json('description')->nullable()->change();
        });

        // Orders table
        DB::table('orders')->update([
            'header' => DB::raw("JSON_SET('{}', '$." . app()->getLocale() . "', header)"),
            'footer' => DB::raw("JSON_SET('{}', '$." . app()->getLocale() . "', footer)"),
            'logistic_note' => DB::raw("JSON_SET('{}', '$." . app()->getLocale() . "', logistic_note)"),
        ]);
        Schema::table('orders', function (Blueprint $table) {
            $table->json('header')->nullable()->change();
            $table->json('footer')->nullable()->change();
            $table->json('logistic_note')->nullable()->change();
        });
    }
};
