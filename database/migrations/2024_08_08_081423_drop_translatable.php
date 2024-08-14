<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Address Types table
        Schema::table('address_types', function (Blueprint $table) {
            $table->string('name_migration')->after('name');
        });
        DB::table('address_types')->update([
            'name_migration' => DB::raw("
                SUBSTRING(
                    COALESCE(
                        NULLIF(
                            JSON_UNQUOTE(
                                JSON_EXTRACT(
                                    name,
                                    CONCAT(
                                        '$.',
                                        JSON_UNQUOTE(
                                            JSON_EXTRACT(
                                                JSON_KEYS(name),
                                                '$[0]'
                                            )
                                        )
                                    )
                                )
                            ),
                            'null'
                        ),
                        ''
                    ),
                    1,
                    255
                )
            "),
        ]);
        Schema::table('address_types', function (Blueprint $table) {
            $table->dropColumn('name');
            $table->renameColumn('name_migration', 'name');
        });

        // Categories table
        Schema::table('categories', function (Blueprint $table) {
            $table->string('name_migration')->after('name');
        });
        DB::table('categories')->update([
            'name_migration' => DB::raw("
                SUBSTRING(
                    COALESCE(
                        NULLIF(
                            JSON_UNQUOTE(
                                JSON_EXTRACT(
                                    name,
                                    CONCAT(
                                        '$.',
                                        JSON_UNQUOTE(
                                            JSON_EXTRACT(
                                                JSON_KEYS(name),
                                                '$[0]'
                                            )
                                        )
                                    )
                                )
                            ),
                            'null'
                        ),
                        ''
                    ),
                    1,
                    255
                )
            "),
        ]);
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('name');
            $table->renameColumn('name_migration', 'name');
        });

        // Countries table
        Schema::table('countries', function (Blueprint $table) {
            $table->string('name_migration')->after('name');
        });
        DB::table('countries')->update([
            'name_migration' => DB::raw("
                SUBSTRING(
                    COALESCE(
                        NULLIF(
                            JSON_UNQUOTE(
                                JSON_EXTRACT(
                                    name,
                                    CONCAT(
                                        '$.',
                                        JSON_UNQUOTE(
                                            JSON_EXTRACT(
                                                JSON_KEYS(name),
                                                '$[0]'
                                            )
                                        )
                                    )
                                )
                            ),
                            'null'
                        ),
                        ''
                    ),
                    1,
                    255
                )
            "),
        ]);
        Schema::table('countries', function (Blueprint $table) {
            $table->dropColumn('name');
            $table->renameColumn('name_migration', 'name');
        });

        // Country Regions table
        Schema::table('country_regions', function (Blueprint $table) {
            $table->string('name_migration')->after('name');
        });
        DB::table('country_regions')->update([
            'name_migration' => DB::raw("
                SUBSTRING(
                    COALESCE(
                        NULLIF(
                            JSON_UNQUOTE(
                                JSON_EXTRACT(
                                    name,
                                    CONCAT(
                                        '$.',
                                        JSON_UNQUOTE(
                                            JSON_EXTRACT(
                                                JSON_KEYS(name),
                                                '$[0]'
                                            )
                                        )
                                    )
                                )
                            ),
                            'null'
                        ),
                        ''
                    ),
                    1,
                    255
                )
            "),
        ]);
        Schema::table('country_regions', function (Blueprint $table) {
            $table->dropColumn('name');
            $table->renameColumn('name_migration', 'name');
        });

        // Languages table
        Schema::table('languages', function (Blueprint $table) {
            $table->string('name_migration')->after('name');
        });
        DB::table('languages')->update([
            'name_migration' => DB::raw("
                SUBSTRING(
                    COALESCE(
                        NULLIF(
                            JSON_UNQUOTE(
                                JSON_EXTRACT(
                                    name,
                                    CONCAT(
                                        '$.',
                                        JSON_UNQUOTE(
                                            JSON_EXTRACT(
                                                JSON_KEYS(name),
                                                '$[0]'
                                            )
                                        )
                                    )
                                )
                            ),
                            'null'
                        ),
                        ''
                    ),
                    1,
                    255
                )
            "),
        ]);
        Schema::table('languages', function (Blueprint $table) {
            $table->dropColumn('name');
            $table->renameColumn('name_migration', 'name');
        });

        // Order Types table
        Schema::table('order_types', function (Blueprint $table) {
            $table->string('name_migration')->after('name');
            $table->string('description_migration')->nullable()->after('description');
        });
        DB::table('order_types')->update([
            'name_migration' => DB::raw("
                SUBSTRING(
                    COALESCE(
                        NULLIF(
                            JSON_UNQUOTE(
                                JSON_EXTRACT(
                                    name,
                                    CONCAT(
                                        '$.',
                                        JSON_UNQUOTE(
                                            JSON_EXTRACT(
                                                JSON_KEYS(name),
                                                '$[0]'
                                            )
                                        )
                                    )
                                )
                            ),
                            'null'
                        ),
                        ''
                    ),
                    1,
                    255
                )
            "),
            'description_migration' => DB::raw("
                NULLIF(
                    JSON_UNQUOTE(
                        JSON_EXTRACT(
                            description,
                            CONCAT(
                                '$.',
                                JSON_UNQUOTE(
                                    JSON_EXTRACT(
                                        JSON_KEYS(description),
                                        '$[0]'
                                    )
                                )
                            )
                        )
                    ),
                    'null'
                )
            "),
        ]);
        Schema::table('order_types', function (Blueprint $table) {
            $table->dropColumn('name');
            $table->dropColumn('description');
            $table->renameColumn('name_migration', 'name');
            $table->renameColumn('description_migration', 'description');
        });

        // Orders table
        Schema::table('orders', function (Blueprint $table) {
            $table->longText('header_migration')->nullable()->after('header');
            $table->longText('footer_migration')->nullable()->after('footer');
            $table->longText('logistic_note_migration')->nullable()->after('logistic_note');
        });
        DB::table('orders')->update([
            'header_migration' => DB::raw("
                NULLIF(
                    JSON_UNQUOTE(
                        JSON_EXTRACT(
                            header,
                            CONCAT(
                                '$.',
                                JSON_UNQUOTE(
                                    JSON_EXTRACT(
                                        JSON_KEYS(header),
                                        '$[0]'
                                    )
                                )
                            )
                        )
                    ),
                    'null'
                )
            "),
            'footer_migration' => DB::raw("
                NULLIF(
                    JSON_UNQUOTE(
                        JSON_EXTRACT(
                            footer,
                            CONCAT(
                                '$.',
                                JSON_UNQUOTE(
                                    JSON_EXTRACT(
                                        JSON_KEYS(footer),
                                        '$[0]'
                                    )
                                )
                            )
                        )
                    ),
                    'null'
                )
            "),
            'logistic_note_migration' => DB::raw("
                NULLIF(
                    JSON_UNQUOTE(
                        JSON_EXTRACT(
                            logistic_note,
                            CONCAT(
                                '$.',
                                JSON_UNQUOTE(
                                    JSON_EXTRACT(
                                        JSON_KEYS(logistic_note),
                                        '$[0]'
                                    )
                                )
                            )
                        )
                    ),
                    'null'
                )
            "),
        ]);
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('header');
            $table->dropColumn('footer');
            $table->dropColumn('logistic_note');
            $table->renameColumn('header_migration', 'header');
            $table->renameColumn('footer_migration', 'footer');
            $table->renameColumn('logistic_note_migration', 'logistic_note');
        });

        // Payment Types table
        Schema::table('payment_types', function (Blueprint $table) {
            $table->string('name_migration')->after('name');
            $table->string('description_migration')->nullable()->after('description');
        });
        DB::table('payment_types')->update([
            'name_migration' => DB::raw("
                SUBSTRING(
                    COALESCE(
                        NULLIF(
                            JSON_UNQUOTE(
                                JSON_EXTRACT(
                                    name,
                                    CONCAT(
                                        '$.',
                                        JSON_UNQUOTE(
                                            JSON_EXTRACT(
                                                JSON_KEYS(name),
                                                '$[0]'
                                            )
                                        )
                                    )
                                )
                            ),
                            'null'
                        ),
                        ''
                    ),
                    1,
                    255
                )
            "),
            'description_migration' => DB::raw("
                NULLIF(
                    JSON_UNQUOTE(
                        JSON_EXTRACT(
                            description,
                            CONCAT(
                                '$.',
                                JSON_UNQUOTE(
                                    JSON_EXTRACT(
                                        JSON_KEYS(description),
                                        '$[0]'
                                    )
                                )
                            )
                        )
                    ),
                    'null'
                )
            "),
        ]);
        Schema::table('payment_types', function (Blueprint $table) {
            $table->dropColumn('name');
            $table->dropColumn('description');
            $table->renameColumn('name_migration', 'name');
            $table->renameColumn('description_migration', 'description');
        });

        // Product Option Groups table
        Schema::table('product_option_groups', function (Blueprint $table) {
            $table->string('name_migration')->after('name');
        });
        DB::table('product_option_groups')->update([
            'name_migration' => DB::raw("
                SUBSTRING(
                    COALESCE(
                        NULLIF(
                            JSON_UNQUOTE(
                                JSON_EXTRACT(
                                    name,
                                    CONCAT(
                                        '$.',
                                        JSON_UNQUOTE(
                                            JSON_EXTRACT(
                                                JSON_KEYS(name),
                                                '$[0]'
                                            )
                                        )
                                    )
                                )
                            ),
                            'null'
                        ),
                        ''
                    ),
                    1,
                    255
                )
            "),
        ]);
        Schema::table('product_option_groups', function (Blueprint $table) {
            $table->dropColumn('name');
            $table->renameColumn('name_migration', 'name');
        });

        // Product Options table
        Schema::table('product_options', function (Blueprint $table) {
            $table->string('name_migration')->after('name');
        });
        DB::table('product_options')->update([
            'name_migration' => DB::raw("
                SUBSTRING(
                    COALESCE(
                        NULLIF(
                            JSON_UNQUOTE(
                                JSON_EXTRACT(
                                    name,
                                    CONCAT(
                                        '$.',
                                        JSON_UNQUOTE(
                                            JSON_EXTRACT(
                                                JSON_KEYS(name),
                                                '$[0]'
                                            )
                                        )
                                    )
                                )
                            ),
                            'null'
                        ),
                        ''
                    ),
                    1,
                    255
                )
            "),
        ]);
        Schema::table('product_options', function (Blueprint $table) {
            $table->dropColumn('name');
            $table->renameColumn('name_migration', 'name');
        });

        // Product Properties table
        Schema::table('product_properties', function (Blueprint $table) {
            $table->string('name_migration')->after('name');
        });
        DB::table('product_properties')->update([
            'name_migration' => DB::raw("
                SUBSTRING(
                    COALESCE(
                        NULLIF(
                            JSON_UNQUOTE(
                                JSON_EXTRACT(
                                    name,
                                    CONCAT(
                                        '$.',
                                        JSON_UNQUOTE(
                                            JSON_EXTRACT(
                                                JSON_KEYS(name),
                                                '$[0]'
                                            )
                                        )
                                    )
                                )
                            ),
                            'null'
                        ),
                        ''
                    ),
                    1,
                    255
                )
            "),
        ]);
        Schema::table('product_properties', function (Blueprint $table) {
            $table->dropColumn('name');
            $table->renameColumn('name_migration', 'name');
        });

        // Products table
        Schema::table('products', function (Blueprint $table) {
            $table->string('name_migration')->nullable()->after('name');
            $table->longText('description_migration')->nullable()->after('description');
        });
        DB::table('products')->update([
            'name_migration' => DB::raw("
                SUBSTRING(
                    NULLIF(
                        JSON_UNQUOTE(
                            JSON_EXTRACT(
                                name,
                                CONCAT(
                                    '$.',
                                    JSON_UNQUOTE(
                                        JSON_EXTRACT(
                                            JSON_KEYS(name),
                                            '$[0]'
                                        )
                                    )
                                )
                            )
                        ),
                        'null'
                    ),
                    1,
                    255
                )
            "),
            'description_migration' => DB::raw("
                NULLIF(
                    JSON_UNQUOTE(
                        JSON_EXTRACT(
                            description,
                            CONCAT(
                                '$.',
                                JSON_UNQUOTE(
                                    JSON_EXTRACT(
                                        JSON_KEYS(description),
                                        '$[0]'
                                    )
                                )
                            )
                        )
                    ),
                    'null'
                )
            "),
        ]);
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('name');
            $table->dropColumn('description');
            $table->renameColumn('name_migration', 'name');
            $table->renameColumn('description_migration', 'description');
        });

        // Ticket Types table
        Schema::table('ticket_types', function (Blueprint $table) {
            $table->string('name_migration')->after('name');
        });
        DB::table('ticket_types')->update([
            'name_migration' => DB::raw("
                SUBSTRING(
                    COALESCE(
                        NULLIF(
                            JSON_UNQUOTE(
                                JSON_EXTRACT(
                                    name,
                                    CONCAT(
                                        '$.',
                                        JSON_UNQUOTE(
                                            JSON_EXTRACT(
                                                JSON_KEYS(name),
                                                '$[0]'
                                            )
                                        )
                                    )
                                )
                            ),
                            'null'
                        ),
                        ''
                    ),
                    1,
                    255
                )
            "),
        ]);
        Schema::table('ticket_types', function (Blueprint $table) {
            $table->dropColumn('name');
            $table->renameColumn('name_migration', 'name');
        });

        // Work Time Types table
        Schema::table('work_time_types', function (Blueprint $table) {
            $table->string('name_migration')->after('name');
        });
        DB::table('work_time_types')->update([
            'name_migration' => DB::raw("
                SUBSTRING(
                    COALESCE(
                        NULLIF(
                            JSON_UNQUOTE(
                                JSON_EXTRACT(
                                    name,
                                    CONCAT(
                                        '$.',
                                        JSON_UNQUOTE(
                                            JSON_EXTRACT(
                                                JSON_KEYS(name),
                                                '$[0]'
                                            )
                                        )
                                    )
                                )
                            ),
                            'null'
                        ),
                        ''
                    ),
                    1,
                    255
                )
            "),
        ]);
        Schema::table('work_time_types', function (Blueprint $table) {
            $table->dropColumn('name');
            $table->renameColumn('name_migration', 'name');
        });
    }

    public function down(): void
    {
        // Work Time Types table
        DB::table('work_time_types')->update([
            'name' => DB::raw("JSON_SET('{}', '$.".app()->getLocale()."', name)"),
        ]);
        Schema::table('work_time_types', function (Blueprint $table) {
            $table->json('name')->change();
        });

        // Ticket Types table
        DB::table('ticket_types')->update([
            'name' => DB::raw("JSON_SET('{}', '$.".app()->getLocale()."', name)"),
        ]);
        Schema::table('ticket_types', function (Blueprint $table) {
            $table->json('name')->change();
        });

        // Products table
        DB::table('products')->update([
            'name' => DB::raw("JSON_SET('{}', '$.".app()->getLocale()."', name)"),
            'description' => DB::raw("JSON_SET('{}', '$.".app()->getLocale()."', description)"),
        ]);
        Schema::table('products', function (Blueprint $table) {
            $table->json('name')->nullable()->change();
            $table->json('description')->nullable()->change();
        });

        // Product Properties table
        DB::table('product_properties')->update([
            'name' => DB::raw("JSON_SET('{}', '$.".app()->getLocale()."', name)"),
        ]);
        Schema::table('product_properties', function (Blueprint $table) {
            $table->json('name')->change();
        });

        // Product Options table
        DB::table('product_options')->update([
            'name' => DB::raw("JSON_SET('{}', '$.".app()->getLocale()."', name)"),
        ]);
        Schema::table('product_options', function (Blueprint $table) {
            $table->json('name')->change();
        });

        // Product Option Groups table
        DB::table('product_option_groups')->update([
            'name' => DB::raw("JSON_SET('{}', '$.".app()->getLocale()."', name)"),
        ]);
        Schema::table('product_option_groups', function (Blueprint $table) {
            $table->json('name')->change();
        });

        // Payment Types table
        DB::table('payment_types')->update([
            'name' => DB::raw("JSON_SET('{}', '$.".app()->getLocale()."', name)"),
            'description' => DB::raw("JSON_SET('{}', '$.".app()->getLocale()."', description)"),
        ]);
        Schema::table('payment_types', function (Blueprint $table) {
            $table->json('name')->change();
            $table->json('description')->nullable()->change();
        });

        // Orders table
        DB::table('orders')->update([
            'header' => DB::raw("JSON_SET('{}', '$.".app()->getLocale()."', header)"),
            'footer' => DB::raw("JSON_SET('{}', '$.".app()->getLocale()."', footer)"),
            'logistic_note' => DB::raw("JSON_SET('{}', '$.".app()->getLocale()."', logistic_note)"),
        ]);
        Schema::table('orders', function (Blueprint $table) {
            $table->json('header')->nullable()->change();
            $table->json('footer')->nullable()->change();
            $table->json('logistic_note')->nullable()->change();
        });

        // Order Types table
        DB::table('order_types')->update([
            'name' => DB::raw("JSON_SET('{}', '$.".app()->getLocale()."', name)"),
            'description' => DB::raw("JSON_SET('{}', '$.".app()->getLocale()."', description)"),
        ]);
        Schema::table('order_types', function (Blueprint $table) {
            $table->json('name')->change();
            $table->json('description')->nullable()->change();
        });

        // Languages table
        DB::table('languages')->update([
            'name' => DB::raw("JSON_SET('{}', '$.".app()->getLocale()."', name)"),
        ]);
        Schema::table('languages', function (Blueprint $table) {
            $table->json('name')->change();
        });

        // Countries table
        DB::table('countries')->update([
            'name' => DB::raw("JSON_SET('{}', '$.".app()->getLocale()."', name)"),
        ]);
        Schema::table('countries', function (Blueprint $table) {
            $table->json('name')->change();
        });

        // Country Regions table
        DB::table('country_regions')->update([
            'name' => DB::raw("JSON_SET('{}', '$.".app()->getLocale()."', name)"),
        ]);
        Schema::table('country_regions', function (Blueprint $table) {
            $table->json('name')->change();
        });

        // Categories table
        DB::table('categories')->update([
            'name' => DB::raw("JSON_SET('{}', '$.".app()->getLocale()."', name)"),
        ]);
        Schema::table('categories', function (Blueprint $table) {
            $table->json('name')->change();
        });

        // Address Types table
        DB::table('address_types')->update([
            'name' => DB::raw("JSON_SET('{}', '$.".app()->getLocale()."', name)"),
        ]);
        Schema::table('address_types', function (Blueprint $table) {
            $table->json('name')->change();
        });
    }
};
