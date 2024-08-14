<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        return;
        DB::statement('INSERT INTO activity_logs (log_name, description, subject_type, event, subject_id, causer_type, causer_id, created_at, updated_at)'.
            'SELECT "model_events", "created", "FluxErp\\\\Models\\\\Address", "created", id, "FluxErp\\\\Models\\\\User", created_by, created_at, created_at FROM addresses WHERE created_by IS NOT NULL');
        DB::statement('INSERT INTO activity_logs (log_name, description, subject_type, event, subject_id, causer_type, causer_id, created_at, updated_at)'.
            'SELECT "model_events", "updated", "FluxErp\\\\Models\\\\Address", "updated", id, "FluxErp\\\\Models\\\\User", updated_by, updated_at, updated_at FROM addresses WHERE updated_by IS NOT NULL');
        DB::statement('INSERT INTO activity_logs (log_name, description, subject_type, event, subject_id, causer_type, causer_id, created_at, updated_at)'.
            'SELECT "model_events", "deleted", "FluxErp\\\\Models\\\\Address", "deleted", id, "FluxErp\\\\Models\\\\User", deleted_by, deleted_at, deleted_at FROM addresses WHERE deleted_by IS NOT NULL');
        Schema::table('addresses', function (Blueprint $table) {
            $table->dropForeign('addresses_created_by_foreign');
            $table->dropForeign('addresses_updated_by_foreign');
            $table->dropForeign('addresses_deleted_by_foreign');
            $table->dropColumn(['created_by', 'updated_by', 'deleted_by']);
        });

        DB::statement('INSERT INTO activity_logs (log_name, description, subject_type, event, subject_id, causer_type, causer_id, created_at, updated_at)'.
            'SELECT "model_events", "created", "FluxErp\\\\Models\\\\AddressType", "created", id, "FluxErp\\\\Models\\\\User", created_by, created_at, created_at FROM address_types WHERE created_by IS NOT NULL');
        DB::statement('INSERT INTO activity_logs (log_name, description, subject_type, event, subject_id, causer_type, causer_id, created_at, updated_at)'.
            'SELECT "model_events", "updated", "FluxErp\\\\Models\\\\AddressType", "updated", id, "FluxErp\\\\Models\\\\User", updated_by, updated_at, updated_at FROM address_types WHERE updated_by IS NOT NULL');
        DB::statement('INSERT INTO activity_logs (log_name, description, subject_type, event, subject_id, causer_type, causer_id, created_at, updated_at)'.
            'SELECT "model_events", "deleted", "FluxErp\\\\Models\\\\AddressType", "deleted", id, "FluxErp\\\\Models\\\\User", deleted_by, deleted_at, deleted_at FROM address_types WHERE deleted_by IS NOT NULL');
        Schema::table('address_types', function (Blueprint $table) {
            $table->dropForeign('address_types_created_by_foreign');
            $table->dropForeign('address_types_updated_by_foreign');
            $table->dropForeign('address_types_deleted_by_foreign');
            $table->dropColumn(['created_by', 'updated_by', 'deleted_by']);
        });

        DB::statement('INSERT INTO activity_logs (log_name, description, subject_type, event, subject_id, causer_type, causer_id, created_at, updated_at)'.
            'SELECT "model_events", "created", "FluxErp\\\\Models\\\\Calendar", "created", id, "FluxErp\\\\Models\\\\User", created_by, created_at, created_at FROM calendars WHERE created_by IS NOT NULL');
        DB::statement('INSERT INTO activity_logs (log_name, description, subject_type, event, subject_id, causer_type, causer_id, created_at, updated_at)'.
            'SELECT "model_events", "updated", "FluxErp\\\\Models\\\\Calendar", "updated", id, "FluxErp\\\\Models\\\\User", updated_by, updated_at, updated_at FROM calendars WHERE updated_by IS NOT NULL');
        Schema::table('calendars', function (Blueprint $table) {
            $table->dropForeign('calendars_created_by_foreign');
            $table->dropForeign('calendars_updated_by_foreign');
            $table->dropColumn(['created_by', 'updated_by']);
        });

        DB::statement('INSERT INTO activity_logs (log_name, description, subject_type, event, subject_id, causer_type, causer_id, created_at, updated_at)'.
            'SELECT "model_events", "created", "FluxErp\\\\Models\\\\CalendarEvent", "created", id, "FluxErp\\\\Models\\\\User", created_by, created_at, created_at FROM calendar_events WHERE created_by IS NOT NULL');
        DB::statement('INSERT INTO activity_logs (log_name, description, subject_type, event, subject_id, causer_type, causer_id, created_at, updated_at)'.
            'SELECT "model_events", "updated", "FluxErp\\\\Models\\\\CalendarEvent", "updated", id, "FluxErp\\\\Models\\\\User", updated_by, updated_at, updated_at FROM calendar_events WHERE updated_by IS NOT NULL');
        Schema::table('calendar_events', function (Blueprint $table) {
            $table->dropForeign('calendar_events_created_by_foreign');
            $table->dropForeign('calendar_events_updated_by_foreign');
            $table->dropColumn(['created_by', 'updated_by']);
        });

        DB::statement('INSERT INTO activity_logs (log_name, description, subject_type, event, subject_id, causer_type, causer_id, created_at, updated_at)'.
            'SELECT "model_events", "created", "FluxErp\\\\Models\\\\Category", "created", id, "FluxErp\\\\Models\\\\User", created_by, created_at, created_at FROM categories WHERE created_by IS NOT NULL');
        DB::statement('INSERT INTO activity_logs (log_name, description, subject_type, event, subject_id, causer_type, causer_id, created_at, updated_at)'.
            'SELECT "model_events", "updated", "FluxErp\\\\Models\\\\Category", "updated", id, "FluxErp\\\\Models\\\\User", updated_by, updated_at, updated_at FROM categories WHERE updated_by IS NOT NULL');
        Schema::table('categories', function (Blueprint $table) {
            $table->dropForeign('categories_created_by_foreign');
            $table->dropForeign('categories_updated_by_foreign');
            $table->dropColumn(['created_by', 'updated_by']);
        });

        DB::statement('INSERT INTO activity_logs (log_name, description, subject_type, event, subject_id, causer_type, causer_id, created_at, updated_at)'.
            'SELECT "model_events", "created", "FluxErp\\\\Models\\\\Client", "created", id, "FluxErp\\\\Models\\\\User", created_by, created_at, created_at FROM clients WHERE created_by IS NOT NULL');
        DB::statement('INSERT INTO activity_logs (log_name, description, subject_type, event, subject_id, causer_type, causer_id, created_at, updated_at)'.
            'SELECT "model_events", "updated", "FluxErp\\\\Models\\\\Client", "updated", id, "FluxErp\\\\Models\\\\User", updated_by, updated_at, updated_at FROM clients WHERE updated_by IS NOT NULL');
        DB::statement('INSERT INTO activity_logs (log_name, description, subject_type, event, subject_id, causer_type, causer_id, created_at, updated_at)'.
            'SELECT "model_events", "deleted", "FluxErp\\\\Models\\\\Client", "deleted", id, "FluxErp\\\\Models\\\\User", deleted_by, deleted_at, deleted_at FROM clients WHERE deleted_by IS NOT NULL');
        Schema::table('clients', function (Blueprint $table) {
            $table->dropForeign('clients_created_by_foreign');
            $table->dropForeign('clients_updated_by_foreign');
            $table->dropForeign('clients_deleted_by_foreign');
            $table->dropColumn(['created_by', 'updated_by', 'deleted_by']);
        });

        DB::statement('INSERT INTO activity_logs (log_name, description, subject_type, event, subject_id, causer_type, causer_id, created_at, updated_at)'.
            'SELECT "model_events", "created", "FluxErp\\\\Models\\\\Comment", "created", id, "FluxErp\\\\Models\\\\User", created_by, created_at, created_at FROM comments WHERE created_by IS NOT NULL');
        DB::statement('INSERT INTO activity_logs (log_name, description, subject_type, event, subject_id, causer_type, causer_id, created_at, updated_at)'.
            'SELECT "model_events", "updated", "FluxErp\\\\Models\\\\Comment", "updated", id, "FluxErp\\\\Models\\\\User", updated_by, updated_at, updated_at FROM comments WHERE updated_by IS NOT NULL');
        DB::statement('INSERT INTO activity_logs (log_name, description, subject_type, event, subject_id, causer_type, causer_id, created_at, updated_at)'.
            'SELECT "model_events", "deleted", "FluxErp\\\\Models\\\\Comment", "deleted", id, "FluxErp\\\\Models\\\\User", deleted_by, deleted_at, deleted_at FROM comments WHERE deleted_by IS NOT NULL');
        Schema::table('comments', function (Blueprint $table) {
            $table->dropForeign('comments_created_by_foreign');
            $table->dropForeign('comments_updated_by_foreign');
            $table->dropForeign('comments_deleted_by_foreign');
            $table->dropColumn(['created_by', 'updated_by', 'deleted_by']);
        });

        DB::statement('INSERT INTO activity_logs (log_name, description, subject_type, event, subject_id, causer_type, causer_id, created_at, updated_at)'.
            'SELECT "model_events", "created", "FluxErp\\\\Models\\\\Contact", "created", id, "FluxErp\\\\Models\\\\User", created_by, created_at, created_at FROM contacts WHERE created_by IS NOT NULL');
        DB::statement('INSERT INTO activity_logs (log_name, description, subject_type, event, subject_id, causer_type, causer_id, created_at, updated_at)'.
            'SELECT "model_events", "updated", "FluxErp\\\\Models\\\\Contact", "updated", id, "FluxErp\\\\Models\\\\User", updated_by, updated_at, updated_at FROM contacts WHERE updated_by IS NOT NULL');
        DB::statement('INSERT INTO activity_logs (log_name, description, subject_type, event, subject_id, causer_type, causer_id, created_at, updated_at)'.
            'SELECT "model_events", "deleted", "FluxErp\\\\Models\\\\Contact", "deleted", id, "FluxErp\\\\Models\\\\User", deleted_by, deleted_at, deleted_at FROM contacts WHERE deleted_by IS NOT NULL');
        Schema::table('contacts', function (Blueprint $table) {
            $table->dropForeign('contacts_created_by_foreign');
            $table->dropForeign('contacts_updated_by_foreign');
            $table->dropForeign('contacts_deleted_by_foreign');
            $table->dropColumn(['created_by', 'updated_by', 'deleted_by']);
        });

        DB::statement('INSERT INTO activity_logs (log_name, description, subject_type, event, subject_id, causer_type, causer_id, created_at, updated_at)'.
            'SELECT "model_events", "created", "FluxErp\\\\Models\\\\ContactBankConnection", "created", id, "FluxErp\\\\Models\\\\User", created_by, created_at, created_at FROM contact_bank_connections WHERE created_by IS NOT NULL');
        DB::statement('INSERT INTO activity_logs (log_name, description, subject_type, event, subject_id, causer_type, causer_id, created_at, updated_at)'.
            'SELECT "model_events", "updated", "FluxErp\\\\Models\\\\ContactBankConnection", "updated", id, "FluxErp\\\\Models\\\\User", updated_by, updated_at, updated_at FROM contact_bank_connections WHERE updated_by IS NOT NULL');
        DB::statement('INSERT INTO activity_logs (log_name, description, subject_type, event, subject_id, causer_type, causer_id, created_at, updated_at)'.
            'SELECT "model_events", "deleted", "FluxErp\\\\Models\\\\ContactBankConnection", "deleted", id, "FluxErp\\\\Models\\\\User", deleted_by, deleted_at, deleted_at FROM contact_bank_connections WHERE deleted_by IS NOT NULL');
        Schema::table('contact_bank_connections', function (Blueprint $table) {
            $table->dropForeign('contact_bank_connections_created_by_foreign');
            $table->dropForeign('contact_bank_connections_updated_by_foreign');
            $table->dropForeign('contact_bank_connections_deleted_by_foreign');
            $table->dropColumn(['created_by', 'updated_by', 'deleted_by']);
        });

        DB::statement('INSERT INTO activity_logs (log_name, description, subject_type, event, subject_id, causer_type, causer_id, created_at, updated_at)'.
            'SELECT "model_events", "created", "FluxErp\\\\Models\\\\ContactOption", "created", id, "FluxErp\\\\Models\\\\User", created_by, created_at, created_at FROM contact_options WHERE created_by IS NOT NULL');
        DB::statement('INSERT INTO activity_logs (log_name, description, subject_type, event, subject_id, causer_type, causer_id, created_at, updated_at)'.
            'SELECT "model_events", "updated", "FluxErp\\\\Models\\\\ContactOption", "updated", id, "FluxErp\\\\Models\\\\User", updated_by, updated_at, updated_at FROM contact_options WHERE updated_by IS NOT NULL');
        Schema::table('contact_options', function (Blueprint $table) {
            $table->dropForeign('contact_options_created_by_foreign');
            $table->dropForeign('contact_options_updated_by_foreign');
            $table->dropColumn(['created_by', 'updated_by']);
        });

        DB::statement('INSERT INTO activity_logs (log_name, description, subject_type, event, subject_id, causer_type, causer_id, created_at, updated_at)'.
            'SELECT "model_events", "created", "FluxErp\\\\Models\\\\Country", "created", id, "FluxErp\\\\Models\\\\User", created_by, created_at, created_at FROM countries WHERE created_by IS NOT NULL');
        DB::statement('INSERT INTO activity_logs (log_name, description, subject_type, event, subject_id, causer_type, causer_id, created_at, updated_at)'.
            'SELECT "model_events", "updated", "FluxErp\\\\Models\\\\Country", "updated", id, "FluxErp\\\\Models\\\\User", updated_by, updated_at, updated_at FROM countries WHERE updated_by IS NOT NULL');
        DB::statement('INSERT INTO activity_logs (log_name, description, subject_type, event, subject_id, causer_type, causer_id, created_at, updated_at)'.
            'SELECT "model_events", "deleted", "FluxErp\\\\Models\\\\Country", "deleted", id, "FluxErp\\\\Models\\\\User", deleted_by, deleted_at, deleted_at FROM countries WHERE deleted_by IS NOT NULL');
        Schema::table('countries', function (Blueprint $table) {
            $table->dropForeign('countries_created_by_foreign');
            $table->dropForeign('countries_updated_by_foreign');
            $table->dropForeign('countries_deleted_by_foreign');
            $table->dropColumn(['created_by', 'updated_by', 'deleted_by']);
        });

        DB::statement('INSERT INTO activity_logs (log_name, description, subject_type, event, subject_id, causer_type, causer_id, created_at, updated_at)'.
            'SELECT "model_events", "created", "FluxErp\\\\Models\\\\CountryRegion", "created", id, "FluxErp\\\\Models\\\\User", created_by, created_at, created_at FROM country_regions WHERE created_by IS NOT NULL');
        DB::statement('INSERT INTO activity_logs (log_name, description, subject_type, event, subject_id, causer_type, causer_id, created_at, updated_at)'.
            'SELECT "model_events", "updated", "FluxErp\\\\Models\\\\CountryRegion", "updated", id, "FluxErp\\\\Models\\\\User", updated_by, updated_at, updated_at FROM country_regions WHERE updated_by IS NOT NULL');
        DB::statement('INSERT INTO activity_logs (log_name, description, subject_type, event, subject_id, causer_type, causer_id, created_at, updated_at)'.
            'SELECT "model_events", "deleted", "FluxErp\\\\Models\\\\CountryRegion", "deleted", id, "FluxErp\\\\Models\\\\User", deleted_by, deleted_at, deleted_at FROM country_regions WHERE deleted_by IS NOT NULL');
        Schema::table('country_regions', function (Blueprint $table) {
            $table->dropForeign('country_regions_created_by_foreign');
            $table->dropForeign('country_regions_updated_by_foreign');
            $table->dropForeign('country_regions_deleted_by_foreign');
            $table->dropColumn(['created_by', 'updated_by', 'deleted_by']);
        });

        DB::statement('INSERT INTO activity_logs (log_name, description, subject_type, event, subject_id, causer_type, causer_id, created_at, updated_at)'.
            'SELECT "model_events", "created", "FluxErp\\\\Models\\\\Currency", "created", id, "FluxErp\\\\Models\\\\User", created_by, created_at, created_at FROM currencies WHERE created_by IS NOT NULL');
        DB::statement('INSERT INTO activity_logs (log_name, description, subject_type, event, subject_id, causer_type, causer_id, created_at, updated_at)'.
            'SELECT "model_events", "updated", "FluxErp\\\\Models\\\\Currency", "updated", id, "FluxErp\\\\Models\\\\User", updated_by, updated_at, updated_at FROM currencies WHERE updated_by IS NOT NULL');
        DB::statement('INSERT INTO activity_logs (log_name, description, subject_type, event, subject_id, causer_type, causer_id, created_at, updated_at)'.
            'SELECT "model_events", "deleted", "FluxErp\\\\Models\\\\Currency", "deleted", id, "FluxErp\\\\Models\\\\User", deleted_by, deleted_at, deleted_at FROM currencies WHERE deleted_by IS NOT NULL');
        Schema::table('currencies', function (Blueprint $table) {
            $table->dropForeign('currencies_created_by_foreign');
            $table->dropForeign('currencies_updated_by_foreign');
            $table->dropForeign('currencies_deleted_by_foreign');
            $table->dropColumn(['created_by', 'updated_by', 'deleted_by']);
        });

        DB::statement('INSERT INTO activity_logs (log_name, description, subject_type, event, subject_id, causer_type, causer_id, created_at, updated_at)'.
            'SELECT "model_events", "created", "FluxErp\\\\Models\\\\Discount", "created", id, "FluxErp\\\\Models\\\\User", created_by, created_at, created_at FROM discounts WHERE created_by IS NOT NULL');
        DB::statement('INSERT INTO activity_logs (log_name, description, subject_type, event, subject_id, causer_type, causer_id, created_at, updated_at)'.
            'SELECT "model_events", "updated", "FluxErp\\\\Models\\\\Discount", "updated", id, "FluxErp\\\\Models\\\\User", updated_by, updated_at, updated_at FROM discounts WHERE updated_by IS NOT NULL');
        DB::statement('INSERT INTO activity_logs (log_name, description, subject_type, event, subject_id, causer_type, causer_id, created_at, updated_at)'.
            'SELECT "model_events", "deleted", "FluxErp\\\\Models\\\\Discount", "deleted", id, "FluxErp\\\\Models\\\\User", deleted_by, deleted_at, deleted_at FROM discounts WHERE deleted_by IS NOT NULL');
        Schema::table('discounts', function (Blueprint $table) {
            $table->dropForeign('discounts_created_by_foreign');
            $table->dropForeign('discounts_updated_by_foreign');
            $table->dropForeign('discounts_deleted_by_foreign');
            $table->dropColumn(['created_by', 'updated_by', 'deleted_by']);
        });

        DB::statement('INSERT INTO activity_logs (log_name, description, subject_type, event, subject_id, causer_type, causer_id, created_at, updated_at)'.
            'SELECT "model_events", "created", "FluxErp\\\\Models\\\\DocumentGenerationSetting", "created", id, "FluxErp\\\\Models\\\\User", created_by, created_at, created_at FROM document_generation_settings WHERE created_by IS NOT NULL');
        DB::statement('INSERT INTO activity_logs (log_name, description, subject_type, event, subject_id, causer_type, causer_id, created_at, updated_at)'.
            'SELECT "model_events", "updated", "FluxErp\\\\Models\\\\DocumentGenerationSetting", "updated", id, "FluxErp\\\\Models\\\\User", updated_by, updated_at, updated_at FROM document_generation_settings WHERE updated_by IS NOT NULL');
        DB::statement('INSERT INTO activity_logs (log_name, description, subject_type, event, subject_id, causer_type, causer_id, created_at, updated_at)'.
            'SELECT "model_events", "deleted", "FluxErp\\\\Models\\\\DocumentGenerationSetting", "deleted", id, "FluxErp\\\\Models\\\\User", deleted_by, deleted_at, deleted_at FROM document_generation_settings WHERE deleted_by IS NOT NULL');
        Schema::table('document_generation_settings', function (Blueprint $table) {
            $table->dropForeign('document_generation_settings_created_by_foreign');
            $table->dropForeign('document_generation_settings_updated_by_foreign');
            $table->dropForeign('document_generation_settings_deleted_by_foreign');
            $table->dropColumn(['created_by', 'updated_by', 'deleted_by']);
        });

        DB::statement('INSERT INTO activity_logs (log_name, description, subject_type, event, subject_id, causer_type, causer_id, created_at, updated_at)'.
            'SELECT "model_events", "created", "FluxErp\\\\Models\\\\DocumentType", "created", id, "FluxErp\\\\Models\\\\User", created_by, created_at, created_at FROM document_types WHERE created_by IS NOT NULL');
        DB::statement('INSERT INTO activity_logs (log_name, description, subject_type, event, subject_id, causer_type, causer_id, created_at, updated_at)'.
            'SELECT "model_events", "updated", "FluxErp\\\\Models\\\\DocumentType", "updated", id, "FluxErp\\\\Models\\\\User", updated_by, updated_at, updated_at FROM document_types WHERE updated_by IS NOT NULL');
        DB::statement('INSERT INTO activity_logs (log_name, description, subject_type, event, subject_id, causer_type, causer_id, created_at, updated_at)'.
            'SELECT "model_events", "deleted", "FluxErp\\\\Models\\\\DocumentType", "deleted", id, "FluxErp\\\\Models\\\\User", deleted_by, deleted_at, deleted_at FROM document_types WHERE deleted_by IS NOT NULL');
        Schema::table('document_types', function (Blueprint $table) {
            $table->dropForeign('document_types_created_by_foreign');
            $table->dropForeign('document_types_updated_by_foreign');
            $table->dropForeign('document_types_deleted_by_foreign');
            $table->dropColumn(['created_by', 'updated_by', 'deleted_by']);
        });

        DB::statement('INSERT INTO activity_logs (log_name, description, subject_type, event, subject_id, causer_type, causer_id, created_at, updated_at)'.
            'SELECT "model_events", "created", "FluxErp\\\\Models\\\\Email", "created", id, "FluxErp\\\\Models\\\\User", created_by, created_at, created_at FROM emails WHERE created_by IS NOT NULL');
        DB::statement('INSERT INTO activity_logs (log_name, description, subject_type, event, subject_id, causer_type, causer_id, created_at, updated_at)'.
            'SELECT "model_events", "updated", "FluxErp\\\\Models\\\\Email", "updated", id, "FluxErp\\\\Models\\\\User", updated_by, updated_at, updated_at FROM emails WHERE updated_by IS NOT NULL');
        DB::statement('INSERT INTO activity_logs (log_name, description, subject_type, event, subject_id, causer_type, causer_id, created_at, updated_at)'.
            'SELECT "model_events", "deleted", "FluxErp\\\\Models\\\\Email", "deleted", id, "FluxErp\\\\Models\\\\User", deleted_by, deleted_at, deleted_at FROM emails WHERE deleted_by IS NOT NULL');
        Schema::table('emails', function (Blueprint $table) {
            $table->dropForeign('emails_created_by_foreign');
            $table->dropForeign('emails_updated_by_foreign');
            $table->dropForeign('emails_deleted_by_foreign');
            $table->dropColumn(['created_by', 'updated_by', 'deleted_by']);
        });

        DB::statement('INSERT INTO activity_logs (log_name, description, subject_type, event, subject_id, causer_type, causer_id, created_at, updated_at)'.
            'SELECT "model_events", "created", "FluxErp\\\\Models\\\\EmailTemplate", "created", id, "FluxErp\\\\Models\\\\User", created_by, created_at, created_at FROM email_templates WHERE created_by IS NOT NULL');
        DB::statement('INSERT INTO activity_logs (log_name, description, subject_type, event, subject_id, causer_type, causer_id, created_at, updated_at)'.
            'SELECT "model_events", "updated", "FluxErp\\\\Models\\\\EmailTemplate", "updated", id, "FluxErp\\\\Models\\\\User", updated_by, updated_at, updated_at FROM email_templates WHERE updated_by IS NOT NULL');
        DB::statement('INSERT INTO activity_logs (log_name, description, subject_type, event, subject_id, causer_type, causer_id, created_at, updated_at)'.
            'SELECT "model_events", "deleted", "FluxErp\\\\Models\\\\EmailTemplate", "deleted", id, "FluxErp\\\\Models\\\\User", deleted_by, deleted_at, deleted_at FROM email_templates WHERE deleted_by IS NOT NULL');
        Schema::table('email_templates', function (Blueprint $table) {
            $table->dropForeign('email_templates_created_by_foreign');
            $table->dropForeign('email_templates_updated_by_foreign');
            $table->dropForeign('email_templates_deleted_by_foreign');
            $table->dropColumn(['created_by', 'updated_by', 'deleted_by']);
        });

        DB::statement('INSERT INTO activity_logs (log_name, description, subject_type, event, subject_id, causer_type, causer_id, created_at, updated_at)'.
            'SELECT "model_events", "created", "FluxErp\\\\Models\\\\InterfaceUser", "created", id, "FluxErp\\\\Models\\\\User", created_by, created_at, created_at FROM interface_users WHERE created_by IS NOT NULL');
        DB::statement('INSERT INTO activity_logs (log_name, description, subject_type, event, subject_id, causer_type, causer_id, created_at, updated_at)'.
            'SELECT "model_events", "updated", "FluxErp\\\\Models\\\\InterfaceUser", "updated", id, "FluxErp\\\\Models\\\\User", updated_by, updated_at, updated_at FROM interface_users WHERE updated_by IS NOT NULL');
        DB::statement('INSERT INTO activity_logs (log_name, description, subject_type, event, subject_id, causer_type, causer_id, created_at, updated_at)'.
            'SELECT "model_events", "deleted", "FluxErp\\\\Models\\\\InterfaceUser", "deleted", id, "FluxErp\\\\Models\\\\User", deleted_by, deleted_at, deleted_at FROM interface_users WHERE deleted_by IS NOT NULL');
        Schema::table('interface_users', function (Blueprint $table) {
            $table->dropForeign('interface_users_created_by_foreign');
            $table->dropForeign('interface_users_updated_by_foreign');
            $table->dropForeign('interface_users_deleted_by_foreign');
            $table->dropColumn(['created_by', 'updated_by', 'deleted_by']);
        });

        DB::statement('INSERT INTO activity_logs (log_name, description, subject_type, event, subject_id, causer_type, causer_id, created_at, updated_at)'.
            'SELECT "model_events", "created", "FluxErp\\\\Models\\\\Language", "created", id, "FluxErp\\\\Models\\\\User", created_by, created_at, created_at FROM languages WHERE created_by IS NOT NULL');
        DB::statement('INSERT INTO activity_logs (log_name, description, subject_type, event, subject_id, causer_type, causer_id, created_at, updated_at)'.
            'SELECT "model_events", "updated", "FluxErp\\\\Models\\\\Language", "updated", id, "FluxErp\\\\Models\\\\User", updated_by, updated_at, updated_at FROM languages WHERE updated_by IS NOT NULL');
        DB::statement('INSERT INTO activity_logs (log_name, description, subject_type, event, subject_id, causer_type, causer_id, created_at, updated_at)'.
            'SELECT "model_events", "deleted", "FluxErp\\\\Models\\\\Language", "deleted", id, "FluxErp\\\\Models\\\\User", deleted_by, deleted_at, deleted_at FROM languages WHERE deleted_by IS NOT NULL');
        Schema::table('languages', function (Blueprint $table) {
            $table->dropForeign('languages_created_by_foreign');
            $table->dropForeign('languages_updated_by_foreign');
            $table->dropForeign('languages_deleted_by_foreign');
            $table->dropColumn(['created_by', 'updated_by', 'deleted_by']);
        });

        DB::statement('INSERT INTO activity_logs (log_name, description, subject_type, event, subject_id, causer_type, causer_id, created_at, updated_at)'.
            'SELECT "model_events", "created", "FluxErp\\\\Models\\\\Lock", "created", id, "FluxErp\\\\Models\\\\User", created_by, created_at, created_at FROM locks WHERE created_by IS NOT NULL');
        DB::statement('INSERT INTO activity_logs (log_name, description, subject_type, event, subject_id, causer_type, causer_id, created_at, updated_at)'.
            'SELECT "model_events", "updated", "FluxErp\\\\Models\\\\Lock", "updated", id, "FluxErp\\\\Models\\\\User", updated_by, updated_at, updated_at FROM locks WHERE updated_by IS NOT NULL');
        Schema::table('locks', function (Blueprint $table) {
            $table->dropForeign('locks_created_by_foreign');
            $table->dropForeign('locks_updated_by_foreign');
            $table->dropColumn(['created_by', 'updated_by']);
        });

        DB::statement('INSERT INTO activity_logs (log_name, description, subject_type, event, subject_id, causer_type, causer_id, created_at, updated_at)'.
            'SELECT "model_events", "created", "FluxErp\\\\Models\\\\Order", "created", id, "FluxErp\\\\Models\\\\User", created_by, created_at, created_at FROM orders WHERE created_by IS NOT NULL');
        DB::statement('INSERT INTO activity_logs (log_name, description, subject_type, event, subject_id, causer_type, causer_id, created_at, updated_at)'.
            'SELECT "model_events", "updated", "FluxErp\\\\Models\\\\Order", "updated", id, "FluxErp\\\\Models\\\\User", updated_by, updated_at, updated_at FROM orders WHERE updated_by IS NOT NULL');
        DB::statement('INSERT INTO activity_logs (log_name, description, subject_type, event, subject_id, causer_type, causer_id, created_at, updated_at)'.
            'SELECT "model_events", "deleted", "FluxErp\\\\Models\\\\Order", "deleted", id, "FluxErp\\\\Models\\\\User", deleted_by, deleted_at, deleted_at FROM orders WHERE deleted_by IS NOT NULL');
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign('orders_created_by_foreign');
            $table->dropForeign('orders_updated_by_foreign');
            $table->dropForeign('orders_deleted_by_foreign');
            $table->dropColumn(['created_by', 'updated_by', 'deleted_by']);
        });

        DB::statement('INSERT INTO activity_logs (log_name, description, subject_type, event, subject_id, causer_type, causer_id, created_at, updated_at)'.
            'SELECT "model_events", "created", "FluxErp\\\\Models\\\\OrderPosition", "created", id, "FluxErp\\\\Models\\\\User", created_by, created_at, created_at FROM order_positions WHERE created_by IS NOT NULL');
        DB::statement('INSERT INTO activity_logs (log_name, description, subject_type, event, subject_id, causer_type, causer_id, created_at, updated_at)'.
            'SELECT "model_events", "updated", "FluxErp\\\\Models\\\\OrderPosition", "updated", id, "FluxErp\\\\Models\\\\User", updated_by, updated_at, updated_at FROM order_positions WHERE updated_by IS NOT NULL');
        DB::statement('INSERT INTO activity_logs (log_name, description, subject_type, event, subject_id, causer_type, causer_id, created_at, updated_at)'.
            'SELECT "model_events", "deleted", "FluxErp\\\\Models\\\\OrderPosition", "deleted", id, "FluxErp\\\\Models\\\\User", deleted_by, deleted_at, deleted_at FROM order_positions WHERE deleted_by IS NOT NULL');
        Schema::table('order_positions', function (Blueprint $table) {
            $table->dropForeign('order_positions_created_by_foreign');
            $table->dropForeign('order_positions_updated_by_foreign');
            $table->dropForeign('order_positions_deleted_by_foreign');
            $table->dropColumn(['created_by', 'updated_by', 'deleted_by']);
        });

        DB::statement('INSERT INTO activity_logs (log_name, description, subject_type, event, subject_id, causer_type, causer_id, created_at, updated_at)'.
            'SELECT "model_events", "created", "FluxErp\\\\Models\\\\OrderType", "created", id, "FluxErp\\\\Models\\\\User", created_by, created_at, created_at FROM order_types WHERE created_by IS NOT NULL');
        DB::statement('INSERT INTO activity_logs (log_name, description, subject_type, event, subject_id, causer_type, causer_id, created_at, updated_at)'.
            'SELECT "model_events", "updated", "FluxErp\\\\Models\\\\OrderType", "updated", id, "FluxErp\\\\Models\\\\User", updated_by, updated_at, updated_at FROM order_types WHERE updated_by IS NOT NULL');
        DB::statement('INSERT INTO activity_logs (log_name, description, subject_type, event, subject_id, causer_type, causer_id, created_at, updated_at)'.
            'SELECT "model_events", "deleted", "FluxErp\\\\Models\\\\OrderType", "deleted", id, "FluxErp\\\\Models\\\\User", deleted_by, deleted_at, deleted_at FROM order_types WHERE deleted_by IS NOT NULL');
        Schema::table('order_types', function (Blueprint $table) {
            $table->dropForeign('order_types_created_by_foreign');
            $table->dropForeign('order_types_updated_by_foreign');
            $table->dropForeign('order_types_deleted_by_foreign');
            $table->dropColumn(['created_by', 'updated_by', 'deleted_by']);
        });

        DB::statement('INSERT INTO activity_logs (log_name, description, subject_type, event, subject_id, causer_type, causer_id, created_at, updated_at)'.
            'SELECT "model_events", "created", "FluxErp\\\\Models\\\\PaymentNotice", "created", id, "FluxErp\\\\Models\\\\User", created_by, created_at, created_at FROM payment_notices WHERE created_by IS NOT NULL');
        DB::statement('INSERT INTO activity_logs (log_name, description, subject_type, event, subject_id, causer_type, causer_id, created_at, updated_at)'.
            'SELECT "model_events", "updated", "FluxErp\\\\Models\\\\PaymentNotice", "updated", id, "FluxErp\\\\Models\\\\User", updated_by, updated_at, updated_at FROM payment_notices WHERE updated_by IS NOT NULL');
        DB::statement('INSERT INTO activity_logs (log_name, description, subject_type, event, subject_id, causer_type, causer_id, created_at, updated_at)'.
            'SELECT "model_events", "deleted", "FluxErp\\\\Models\\\\PaymentNotice", "deleted", id, "FluxErp\\\\Models\\\\User", deleted_by, deleted_at, deleted_at FROM payment_notices WHERE deleted_by IS NOT NULL');
        Schema::table('payment_notices', function (Blueprint $table) {
            $table->dropForeign('payment_notices_created_by_foreign');
            $table->dropForeign('payment_notices_updated_by_foreign');
            $table->dropForeign('payment_notices_deleted_by_foreign');
            $table->dropColumn(['created_by', 'updated_by', 'deleted_by']);
        });

        DB::statement('INSERT INTO activity_logs (log_name, description, subject_type, event, subject_id, causer_type, causer_id, created_at, updated_at)'.
            'SELECT "model_events", "created", "FluxErp\\\\Models\\\\PaymentType", "created", id, "FluxErp\\\\Models\\\\User", created_by, created_at, created_at FROM payment_types WHERE created_by IS NOT NULL');
        DB::statement('INSERT INTO activity_logs (log_name, description, subject_type, event, subject_id, causer_type, causer_id, created_at, updated_at)'.
            'SELECT "model_events", "updated", "FluxErp\\\\Models\\\\PaymentType", "updated", id, "FluxErp\\\\Models\\\\User", updated_by, updated_at, updated_at FROM payment_types WHERE updated_by IS NOT NULL');
        DB::statement('INSERT INTO activity_logs (log_name, description, subject_type, event, subject_id, causer_type, causer_id, created_at, updated_at)'.
            'SELECT "model_events", "deleted", "FluxErp\\\\Models\\\\PaymentType", "deleted", id, "FluxErp\\\\Models\\\\User", deleted_by, deleted_at, deleted_at FROM payment_types WHERE deleted_by IS NOT NULL');
        Schema::table('payment_types', function (Blueprint $table) {
            $table->dropForeign('payment_types_created_by_foreign');
            $table->dropForeign('payment_types_updated_by_foreign');
            $table->dropForeign('payment_types_deleted_by_foreign');
            $table->dropColumn(['created_by', 'updated_by', 'deleted_by']);
        });

        DB::statement('INSERT INTO activity_logs (log_name, description, subject_type, event, subject_id, causer_type, causer_id, created_at, updated_at)'.
            'SELECT "model_events", "created", "FluxErp\\\\Models\\\\Presentation", "created", id, "FluxErp\\\\Models\\\\User", created_by, created_at, created_at FROM presentations WHERE created_by IS NOT NULL');
        DB::statement('INSERT INTO activity_logs (log_name, description, subject_type, event, subject_id, causer_type, causer_id, created_at, updated_at)'.
            'SELECT "model_events", "updated", "FluxErp\\\\Models\\\\Presentation", "updated", id, "FluxErp\\\\Models\\\\User", updated_by, updated_at, updated_at FROM presentations WHERE updated_by IS NOT NULL');
        DB::statement('INSERT INTO activity_logs (log_name, description, subject_type, event, subject_id, causer_type, causer_id, created_at, updated_at)'.
            'SELECT "model_events", "deleted", "FluxErp\\\\Models\\\\Presentation", "deleted", id, "FluxErp\\\\Models\\\\User", deleted_by, deleted_at, deleted_at FROM presentations WHERE deleted_by IS NOT NULL');
        Schema::table('presentations', function (Blueprint $table) {
            $table->dropForeign('presentations_created_by_foreign');
            $table->dropForeign('presentations_updated_by_foreign');
            $table->dropForeign('presentations_deleted_by_foreign');
            $table->dropColumn(['created_by', 'updated_by', 'deleted_by']);
        });

        DB::statement('INSERT INTO activity_logs (log_name, description, subject_type, event, subject_id, causer_type, causer_id, created_at, updated_at)'.
            'SELECT "model_events", "created", "FluxErp\\\\Models\\\\Price", "created", id, "FluxErp\\\\Models\\\\User", created_by, created_at, created_at FROM prices WHERE created_by IS NOT NULL');
        DB::statement('INSERT INTO activity_logs (log_name, description, subject_type, event, subject_id, causer_type, causer_id, created_at, updated_at)'.
            'SELECT "model_events", "updated", "FluxErp\\\\Models\\\\Price", "updated", id, "FluxErp\\\\Models\\\\User", updated_by, updated_at, updated_at FROM prices WHERE updated_by IS NOT NULL');
        DB::statement('INSERT INTO activity_logs (log_name, description, subject_type, event, subject_id, causer_type, causer_id, created_at, updated_at)'.
            'SELECT "model_events", "deleted", "FluxErp\\\\Models\\\\Price", "deleted", id, "FluxErp\\\\Models\\\\User", deleted_by, deleted_at, deleted_at FROM prices WHERE deleted_by IS NOT NULL');
        Schema::table('prices', function (Blueprint $table) {
            $table->dropForeign('prices_created_by_foreign');
            $table->dropForeign('prices_updated_by_foreign');
            $table->dropForeign('prices_deleted_by_foreign');
            $table->dropColumn(['created_by', 'updated_by', 'deleted_by']);
        });

        DB::statement('INSERT INTO activity_logs (log_name, description, subject_type, event, subject_id, causer_type, causer_id, created_at, updated_at)'.
            'SELECT "model_events", "created", "FluxErp\\\\Models\\\\PriceList", "created", id, "FluxErp\\\\Models\\\\User", created_by, created_at, created_at FROM price_lists WHERE created_by IS NOT NULL');
        DB::statement('INSERT INTO activity_logs (log_name, description, subject_type, event, subject_id, causer_type, causer_id, created_at, updated_at)'.
            'SELECT "model_events", "updated", "FluxErp\\\\Models\\\\PriceList", "updated", id, "FluxErp\\\\Models\\\\User", updated_by, updated_at, updated_at FROM price_lists WHERE updated_by IS NOT NULL');
        DB::statement('INSERT INTO activity_logs (log_name, description, subject_type, event, subject_id, causer_type, causer_id, created_at, updated_at)'.
            'SELECT "model_events", "deleted", "FluxErp\\\\Models\\\\PriceList", "deleted", id, "FluxErp\\\\Models\\\\User", deleted_by, deleted_at, deleted_at FROM price_lists WHERE deleted_by IS NOT NULL');
        Schema::table('price_lists', function (Blueprint $table) {
            $table->dropForeign('price_lists_created_by_foreign');
            $table->dropForeign('price_lists_updated_by_foreign');
            $table->dropForeign('price_lists_deleted_by_foreign');
            $table->dropColumn(['created_by', 'updated_by', 'deleted_by']);
        });

        DB::statement('INSERT INTO activity_logs (log_name, description, subject_type, event, subject_id, causer_type, causer_id, created_at, updated_at)'.
            'SELECT "model_events", "created", "FluxErp\\\\Models\\\\PrintData", "created", id, "FluxErp\\\\Models\\\\User", created_by, created_at, created_at FROM print_data WHERE created_by IS NOT NULL');
        DB::statement('INSERT INTO activity_logs (log_name, description, subject_type, event, subject_id, causer_type, causer_id, created_at, updated_at)'.
            'SELECT "model_events", "updated", "FluxErp\\\\Models\\\\PrintData", "updated", id, "FluxErp\\\\Models\\\\User", updated_by, updated_at, updated_at FROM print_data WHERE updated_by IS NOT NULL');
        Schema::table('print_data', function (Blueprint $table) {
            $table->dropForeign('print_data_created_by_foreign');
            $table->dropForeign('print_data_updated_by_foreign');
            $table->dropColumn(['created_by', 'updated_by']);
        });

        DB::statement('INSERT INTO activity_logs (log_name, description, subject_type, event, subject_id, causer_type, causer_id, created_at, updated_at)'.
            'SELECT "model_events", "created", "FluxErp\\\\Models\\\\Product", "created", id, "FluxErp\\\\Models\\\\User", created_by, created_at, created_at FROM products WHERE created_by IS NOT NULL');
        DB::statement('INSERT INTO activity_logs (log_name, description, subject_type, event, subject_id, causer_type, causer_id, created_at, updated_at)'.
            'SELECT "model_events", "updated", "FluxErp\\\\Models\\\\Product", "updated", id, "FluxErp\\\\Models\\\\User", updated_by, updated_at, updated_at FROM products WHERE updated_by IS NOT NULL');
        DB::statement('INSERT INTO activity_logs (log_name, description, subject_type, event, subject_id, causer_type, causer_id, created_at, updated_at)'.
            'SELECT "model_events", "deleted", "FluxErp\\\\Models\\\\Product", "deleted", id, "FluxErp\\\\Models\\\\User", deleted_by, deleted_at, deleted_at FROM products WHERE deleted_by IS NOT NULL');
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign('products_created_by_foreign');
            $table->dropForeign('products_updated_by_foreign');
            $table->dropForeign('products_deleted_by_foreign');
            $table->dropColumn(['created_by', 'updated_by', 'deleted_by']);
        });

        DB::statement('INSERT INTO activity_logs (log_name, description, subject_type, event, subject_id, causer_type, causer_id, created_at, updated_at)'.
            'SELECT "model_events", "created", "FluxErp\\\\Models\\\\ProductOption", "created", id, "FluxErp\\\\Models\\\\User", created_by, created_at, created_at FROM product_options WHERE created_by IS NOT NULL');
        DB::statement('INSERT INTO activity_logs (log_name, description, subject_type, event, subject_id, causer_type, causer_id, created_at, updated_at)'.
            'SELECT "model_events", "updated", "FluxErp\\\\Models\\\\ProductOption", "updated", id, "FluxErp\\\\Models\\\\User", updated_by, updated_at, updated_at FROM product_options WHERE updated_by IS NOT NULL');
        DB::statement('INSERT INTO activity_logs (log_name, description, subject_type, event, subject_id, causer_type, causer_id, created_at, updated_at)'.
            'SELECT "model_events", "deleted", "FluxErp\\\\Models\\\\ProductOption", "deleted", id, "FluxErp\\\\Models\\\\User", deleted_by, deleted_at, deleted_at FROM product_options WHERE deleted_by IS NOT NULL');
        Schema::table('product_options', function (Blueprint $table) {
            $table->dropForeign('product_options_created_by_foreign');
            $table->dropForeign('product_options_updated_by_foreign');
            $table->dropForeign('product_options_deleted_by_foreign');
            $table->dropColumn(['created_by', 'updated_by', 'deleted_by']);
        });

        DB::statement('INSERT INTO activity_logs (log_name, description, subject_type, event, subject_id, causer_type, causer_id, created_at, updated_at)'.
            'SELECT "model_events", "created", "FluxErp\\\\Models\\\\ProductOptionGroup", "created", id, "FluxErp\\\\Models\\\\User", created_by, created_at, created_at FROM product_option_groups WHERE created_by IS NOT NULL');
        DB::statement('INSERT INTO activity_logs (log_name, description, subject_type, event, subject_id, causer_type, causer_id, created_at, updated_at)'.
            'SELECT "model_events", "updated", "FluxErp\\\\Models\\\\ProductOptionGroup", "updated", id, "FluxErp\\\\Models\\\\User", updated_by, updated_at, updated_at FROM product_option_groups WHERE updated_by IS NOT NULL');
        DB::statement('INSERT INTO activity_logs (log_name, description, subject_type, event, subject_id, causer_type, causer_id, created_at, updated_at)'.
            'SELECT "model_events", "deleted", "FluxErp\\\\Models\\\\ProductOptionGroup", "deleted", id, "FluxErp\\\\Models\\\\User", deleted_by, deleted_at, deleted_at FROM product_option_groups WHERE deleted_by IS NOT NULL');
        Schema::table('product_option_groups', function (Blueprint $table) {
            $table->dropForeign('product_option_groups_created_by_foreign');
            $table->dropForeign('product_option_groups_updated_by_foreign');
            $table->dropForeign('product_option_groups_deleted_by_foreign');
            $table->dropColumn(['created_by', 'updated_by', 'deleted_by']);
        });

        DB::statement('INSERT INTO activity_logs (log_name, description, subject_type, event, subject_id, causer_type, causer_id, created_at, updated_at)'.
            'SELECT "model_events", "created", "FluxErp\\\\Models\\\\ProductProperty", "created", id, "FluxErp\\\\Models\\\\User", created_by, created_at, created_at FROM product_properties WHERE created_by IS NOT NULL');
        DB::statement('INSERT INTO activity_logs (log_name, description, subject_type, event, subject_id, causer_type, causer_id, created_at, updated_at)'.
            'SELECT "model_events", "updated", "FluxErp\\\\Models\\\\ProductProperty", "updated", id, "FluxErp\\\\Models\\\\User", updated_by, updated_at, updated_at FROM product_properties WHERE updated_by IS NOT NULL');
        DB::statement('INSERT INTO activity_logs (log_name, description, subject_type, event, subject_id, causer_type, causer_id, created_at, updated_at)'.
            'SELECT "model_events", "deleted", "FluxErp\\\\Models\\\\ProductProperty", "deleted", id, "FluxErp\\\\Models\\\\User", deleted_by, deleted_at, deleted_at FROM product_properties WHERE deleted_by IS NOT NULL');
        Schema::table('product_properties', function (Blueprint $table) {
            $table->dropForeign('product_properties_created_by_foreign');
            $table->dropForeign('product_properties_updated_by_foreign');
            $table->dropForeign('product_properties_deleted_by_foreign');
            $table->dropColumn(['created_by', 'updated_by', 'deleted_by']);
        });

        DB::statement('INSERT INTO activity_logs (log_name, description, subject_type, event, subject_id, causer_type, causer_id, created_at, updated_at)'.
            'SELECT "model_events", "created", "FluxErp\\\\Models\\\\Project", "created", id, "FluxErp\\\\Models\\\\User", created_by, created_at, created_at FROM projects WHERE created_by IS NOT NULL');
        DB::statement('INSERT INTO activity_logs (log_name, description, subject_type, event, subject_id, causer_type, causer_id, created_at, updated_at)'.
            'SELECT "model_events", "updated", "FluxErp\\\\Models\\\\Project", "updated", id, "FluxErp\\\\Models\\\\User", updated_by, updated_at, updated_at FROM projects WHERE updated_by IS NOT NULL');
        DB::statement('INSERT INTO activity_logs (log_name, description, subject_type, event, subject_id, causer_type, causer_id, created_at, updated_at)'.
            'SELECT "model_events", "deleted", "FluxErp\\\\Models\\\\Project", "deleted", id, "FluxErp\\\\Models\\\\User", deleted_by, deleted_at, deleted_at FROM projects WHERE deleted_by IS NOT NULL');
        Schema::table('projects', function (Blueprint $table) {
            $table->dropForeign('projects_created_by_foreign');
            $table->dropForeign('projects_updated_by_foreign');
            $table->dropForeign('projects_deleted_by_foreign');
            $table->dropColumn(['created_by', 'updated_by', 'deleted_by']);
        });

        DB::statement('INSERT INTO activity_logs (log_name, description, subject_type, event, subject_id, causer_type, causer_id, created_at, updated_at)'.
            'SELECT "model_events", "created", "FluxErp\\\\Models\\\\ProjectCategoryTemplate", "created", id, "FluxErp\\\\Models\\\\User", created_by, created_at, created_at FROM project_category_templates WHERE created_by IS NOT NULL');
        DB::statement('INSERT INTO activity_logs (log_name, description, subject_type, event, subject_id, causer_type, causer_id, created_at, updated_at)'.
            'SELECT "model_events", "updated", "FluxErp\\\\Models\\\\ProjectCategoryTemplate", "updated", id, "FluxErp\\\\Models\\\\User", updated_by, updated_at, updated_at FROM project_category_templates WHERE updated_by IS NOT NULL');
        Schema::table('project_category_templates', function (Blueprint $table) {
            $table->dropForeign('project_category_templates_created_by_foreign');
            $table->dropForeign('project_category_templates_updated_by_foreign');
            $table->dropColumn(['created_by', 'updated_by']);
        });

        DB::statement('INSERT INTO activity_logs (log_name, description, subject_type, event, subject_id, causer_type, causer_id, created_at, updated_at)'.
            'SELECT "model_events", "created", "FluxErp\\\\Models\\\\ProjectTask", "created", id, "FluxErp\\\\Models\\\\User", created_by, created_at, created_at FROM project_tasks WHERE created_by IS NOT NULL');
        DB::statement('INSERT INTO activity_logs (log_name, description, subject_type, event, subject_id, causer_type, causer_id, created_at, updated_at)'.
            'SELECT "model_events", "updated", "FluxErp\\\\Models\\\\ProjectTask", "updated", id, "FluxErp\\\\Models\\\\User", updated_by, updated_at, updated_at FROM project_tasks WHERE updated_by IS NOT NULL');
        DB::statement('INSERT INTO activity_logs (log_name, description, subject_type, event, subject_id, causer_type, causer_id, created_at, updated_at)'.
            'SELECT "model_events", "deleted", "FluxErp\\\\Models\\\\ProjectTask", "deleted", id, "FluxErp\\\\Models\\\\User", deleted_by, deleted_at, deleted_at FROM project_tasks WHERE deleted_by IS NOT NULL');
        Schema::table('project_tasks', function (Blueprint $table) {
            $table->dropForeign('project_tasks_created_by_foreign');
            $table->dropForeign('project_tasks_updated_by_foreign');
            $table->dropForeign('project_tasks_deleted_by_foreign');
            $table->dropColumn(['created_by', 'updated_by', 'deleted_by']);
        });

        DB::statement('INSERT INTO activity_logs (log_name, description, subject_type, event, subject_id, causer_type, causer_id, created_at, updated_at)'.
            'SELECT "model_events", "created", "FluxErp\\\\Models\\\\SepaMandate", "created", id, "FluxErp\\\\Models\\\\User", created_by, created_at, created_at FROM sepa_mandates WHERE created_by IS NOT NULL');
        DB::statement('INSERT INTO activity_logs (log_name, description, subject_type, event, subject_id, causer_type, causer_id, created_at, updated_at)'.
            'SELECT "model_events", "updated", "FluxErp\\\\Models\\\\SepaMandate", "updated", id, "FluxErp\\\\Models\\\\User", updated_by, updated_at, updated_at FROM sepa_mandates WHERE updated_by IS NOT NULL');
        DB::statement('INSERT INTO activity_logs (log_name, description, subject_type, event, subject_id, causer_type, causer_id, created_at, updated_at)'.
            'SELECT "model_events", "deleted", "FluxErp\\\\Models\\\\SepaMandate", "deleted", id, "FluxErp\\\\Models\\\\User", deleted_by, deleted_at, deleted_at FROM sepa_mandates WHERE deleted_by IS NOT NULL');
        Schema::table('sepa_mandates', function (Blueprint $table) {
            $table->dropForeign('sepa_mandates_created_by_foreign');
            $table->dropForeign('sepa_mandates_updated_by_foreign');
            $table->dropForeign('sepa_mandates_deleted_by_foreign');
            $table->dropColumn(['created_by', 'updated_by', 'deleted_by']);
        });

        DB::statement('INSERT INTO activity_logs (log_name, description, subject_type, event, subject_id, causer_type, causer_id, created_at, updated_at)'.
            'SELECT "model_events", "created", "FluxErp\\\\Models\\\\SerialNumber", "created", id, "FluxErp\\\\Models\\\\User", created_by, created_at, created_at FROM serial_numbers WHERE created_by IS NOT NULL');
        DB::statement('INSERT INTO activity_logs (log_name, description, subject_type, event, subject_id, causer_type, causer_id, created_at, updated_at)'.
            'SELECT "model_events", "updated", "FluxErp\\\\Models\\\\SerialNumber", "updated", id, "FluxErp\\\\Models\\\\User", updated_by, updated_at, updated_at FROM serial_numbers WHERE updated_by IS NOT NULL');
        Schema::table('serial_numbers', function (Blueprint $table) {
            $table->dropForeign('serial_numbers_created_by_foreign');
            $table->dropForeign('serial_numbers_updated_by_foreign');
            $table->dropColumn(['created_by', 'updated_by']);
        });

        DB::statement('INSERT INTO activity_logs (log_name, description, subject_type, event, subject_id, causer_type, causer_id, created_at, updated_at)'.
            'SELECT "model_events", "created", "FluxErp\\\\Models\\\\SerialNumberRange", "created", id, "FluxErp\\\\Models\\\\User", created_by, created_at, created_at FROM serial_number_ranges WHERE created_by IS NOT NULL');
        DB::statement('INSERT INTO activity_logs (log_name, description, subject_type, event, subject_id, causer_type, causer_id, created_at, updated_at)'.
            'SELECT "model_events", "updated", "FluxErp\\\\Models\\\\SerialNumberRange", "updated", id, "FluxErp\\\\Models\\\\User", updated_by, updated_at, updated_at FROM serial_number_ranges WHERE updated_by IS NOT NULL');
        DB::statement('INSERT INTO activity_logs (log_name, description, subject_type, event, subject_id, causer_type, causer_id, created_at, updated_at)'.
            'SELECT "model_events", "deleted", "FluxErp\\\\Models\\\\SerialNumberRange", "deleted", id, "FluxErp\\\\Models\\\\User", deleted_by, deleted_at, deleted_at FROM serial_number_ranges WHERE deleted_by IS NOT NULL');
        Schema::table('serial_number_ranges', function (Blueprint $table) {
            $table->dropForeign('serial_number_ranges_created_by_foreign');
            $table->dropForeign('serial_number_ranges_updated_by_foreign');
            $table->dropForeign('serial_number_ranges_deleted_by_foreign');
            $table->dropColumn(['created_by', 'updated_by', 'deleted_by']);
        });

        DB::statement('INSERT INTO activity_logs (log_name, description, subject_type, event, subject_id, causer_type, causer_id, created_at, updated_at)'.
            'SELECT "model_events", "created", "FluxErp\\\\Models\\\\Snapshot", "created", id, "FluxErp\\\\Models\\\\User", created_by, created_at, created_at FROM snapshots WHERE created_by IS NOT NULL');
        DB::statement('INSERT INTO activity_logs (log_name, description, subject_type, event, subject_id, causer_type, causer_id, created_at, updated_at)'.
            'SELECT "model_events", "updated", "FluxErp\\\\Models\\\\Snapshot", "updated", id, "FluxErp\\\\Models\\\\User", updated_by, updated_at, updated_at FROM snapshots WHERE updated_by IS NOT NULL');
        Schema::table('snapshots', function (Blueprint $table) {
            $table->dropForeign('snapshots_created_by_foreign');
            $table->dropForeign('snapshots_updated_by_foreign');
            $table->dropColumn(['created_by', 'updated_by']);
        });

        DB::statement('INSERT INTO activity_logs (log_name, description, subject_type, event, subject_id, causer_type, causer_id, created_at, updated_at)'.
            'SELECT "model_events", "created", "FluxErp\\\\Models\\\\StockPosting", "created", id, "FluxErp\\\\Models\\\\User", created_by, created_at, created_at FROM stock_postings WHERE created_by IS NOT NULL');
        DB::statement('INSERT INTO activity_logs (log_name, description, subject_type, event, subject_id, causer_type, causer_id, created_at, updated_at)'.
            'SELECT "model_events", "updated", "FluxErp\\\\Models\\\\StockPosting", "updated", id, "FluxErp\\\\Models\\\\User", updated_by, updated_at, updated_at FROM stock_postings WHERE updated_by IS NOT NULL');
        DB::statement('INSERT INTO activity_logs (log_name, description, subject_type, event, subject_id, causer_type, causer_id, created_at, updated_at)'.
            'SELECT "model_events", "deleted", "FluxErp\\\\Models\\\\StockPosting", "deleted", id, "FluxErp\\\\Models\\\\User", deleted_by, deleted_at, deleted_at FROM stock_postings WHERE deleted_by IS NOT NULL');
        Schema::table('stock_postings', function (Blueprint $table) {
            $table->dropForeign('stock_postings_created_by_foreign');
            $table->dropForeign('stock_postings_updated_by_foreign');
            $table->dropForeign('stock_postings_deleted_by_foreign');
            $table->dropColumn(['created_by', 'updated_by', 'deleted_by']);
        });

        DB::statement('INSERT INTO activity_logs (log_name, description, subject_type, event, subject_id, causer_type, causer_id, created_at, updated_at)'.
            'SELECT "model_events", "created", "FluxErp\\\\Models\\\\Ticket", "created", id, "FluxErp\\\\Models\\\\User", created_by, created_at, created_at FROM tickets WHERE created_by IS NOT NULL');
        DB::statement('INSERT INTO activity_logs (log_name, description, subject_type, event, subject_id, causer_type, causer_id, created_at, updated_at)'.
            'SELECT "model_events", "updated", "FluxErp\\\\Models\\\\Ticket", "updated", id, "FluxErp\\\\Models\\\\User", updated_by, updated_at, updated_at FROM tickets WHERE updated_by IS NOT NULL');
        DB::statement('INSERT INTO activity_logs (log_name, description, subject_type, event, subject_id, causer_type, causer_id, created_at, updated_at)'.
            'SELECT "model_events", "deleted", "FluxErp\\\\Models\\\\Ticket", "deleted", id, "FluxErp\\\\Models\\\\User", deleted_by, deleted_at, deleted_at FROM tickets WHERE deleted_by IS NOT NULL');
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropForeign('tickets_created_by_foreign');
            $table->dropForeign('tickets_updated_by_foreign');
            $table->dropForeign('tickets_deleted_by_foreign');
            $table->dropColumn(['created_by', 'updated_by', 'deleted_by']);
        });

        DB::statement('INSERT INTO activity_logs (log_name, description, subject_type, event, subject_id, causer_type, causer_id, created_at, updated_at)'.
            'SELECT "model_events", "created", "FluxErp\\\\Models\\\\TicketType", "created", id, "FluxErp\\\\Models\\\\User", created_by, created_at, created_at FROM ticket_types WHERE created_by IS NOT NULL');
        DB::statement('INSERT INTO activity_logs (log_name, description, subject_type, event, subject_id, causer_type, causer_id, created_at, updated_at)'.
            'SELECT "model_events", "updated", "FluxErp\\\\Models\\\\TicketType", "updated", id, "FluxErp\\\\Models\\\\User", updated_by, updated_at, updated_at FROM ticket_types WHERE updated_by IS NOT NULL');
        DB::statement('INSERT INTO activity_logs (log_name, description, subject_type, event, subject_id, causer_type, causer_id, created_at, updated_at)'.
            'SELECT "model_events", "deleted", "FluxErp\\\\Models\\\\TicketType", "deleted", id, "FluxErp\\\\Models\\\\User", deleted_by, deleted_at, deleted_at FROM ticket_types WHERE deleted_by IS NOT NULL');
        Schema::table('ticket_types', function (Blueprint $table) {
            $table->dropColumn(['created_by', 'updated_by', 'deleted_by']);
        });

        DB::statement('INSERT INTO activity_logs (log_name, description, subject_type, event, subject_id, causer_type, causer_id, created_at, updated_at)'.
            'SELECT "model_events", "created", "FluxErp\\\\Models\\\\Unit", "created", id, "FluxErp\\\\Models\\\\User", created_by, created_at, created_at FROM units WHERE created_by IS NOT NULL');
        DB::statement('INSERT INTO activity_logs (log_name, description, subject_type, event, subject_id, causer_type, causer_id, created_at, updated_at)'.
            'SELECT "model_events", "updated", "FluxErp\\\\Models\\\\Unit", "updated", id, "FluxErp\\\\Models\\\\User", updated_by, updated_at, updated_at FROM units WHERE updated_by IS NOT NULL');
        DB::statement('INSERT INTO activity_logs (log_name, description, subject_type, event, subject_id, causer_type, causer_id, created_at, updated_at)'.
            'SELECT "model_events", "deleted", "FluxErp\\\\Models\\\\Unit", "deleted", id, "FluxErp\\\\Models\\\\User", deleted_by, deleted_at, deleted_at FROM units WHERE deleted_by IS NOT NULL');
        Schema::table('units', function (Blueprint $table) {
            $table->dropForeign('units_created_by_foreign');
            $table->dropForeign('units_updated_by_foreign');
            $table->dropForeign('units_deleted_by_foreign');
            $table->dropColumn(['created_by', 'updated_by', 'deleted_by']);
        });

        DB::statement('INSERT INTO activity_logs (log_name, description, subject_type, event, subject_id, causer_type, causer_id, created_at, updated_at)'.
            'SELECT "model_events", "created", "FluxErp\\\\Models\\\\User", "created", id, "FluxErp\\\\Models\\\\User", created_by, created_at, created_at FROM users WHERE created_by IS NOT NULL');
        DB::statement('INSERT INTO activity_logs (log_name, description, subject_type, event, subject_id, causer_type, causer_id, created_at, updated_at)'.
            'SELECT "model_events", "updated", "FluxErp\\\\Models\\\\User", "updated", id, "FluxErp\\\\Models\\\\User", updated_by, updated_at, updated_at FROM users WHERE updated_by IS NOT NULL');
        DB::statement('INSERT INTO activity_logs (log_name, description, subject_type, event, subject_id, causer_type, causer_id, created_at, updated_at)'.
            'SELECT "model_events", "deleted", "FluxErp\\\\Models\\\\User", "deleted", id, "FluxErp\\\\Models\\\\User", deleted_by, deleted_at, deleted_at FROM users WHERE deleted_by IS NOT NULL');
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign('users_created_by_foreign');
            $table->dropForeign('users_updated_by_foreign');
            $table->dropForeign('users_deleted_by_foreign');
            $table->dropColumn(['created_by', 'updated_by', 'deleted_by']);
        });

        DB::statement('INSERT INTO activity_logs (log_name, description, subject_type, event, subject_id, causer_type, causer_id, created_at, updated_at)'.
            'SELECT "model_events", "created", "FluxErp\\\\Models\\\\VatRate", "created", id, "FluxErp\\\\Models\\\\User", created_by, created_at, created_at FROM vat_rates WHERE created_by IS NOT NULL');
        DB::statement('INSERT INTO activity_logs (log_name, description, subject_type, event, subject_id, causer_type, causer_id, created_at, updated_at)'.
            'SELECT "model_events", "updated", "FluxErp\\\\Models\\\\VatRate", "updated", id, "FluxErp\\\\Models\\\\User", updated_by, updated_at, updated_at FROM vat_rates WHERE updated_by IS NOT NULL');
        DB::statement('INSERT INTO activity_logs (log_name, description, subject_type, event, subject_id, causer_type, causer_id, created_at, updated_at)'.
            'SELECT "model_events", "deleted", "FluxErp\\\\Models\\\\VatRate", "deleted", id, "FluxErp\\\\Models\\\\User", deleted_by, deleted_at, deleted_at FROM vat_rates WHERE deleted_by IS NOT NULL');
        Schema::table('vat_rates', function (Blueprint $table) {
            $table->dropForeign('vat_rates_created_by_foreign');
            $table->dropForeign('vat_rates_updated_by_foreign');
            $table->dropForeign('vat_rates_deleted_by_foreign');
            $table->dropColumn(['created_by', 'updated_by', 'deleted_by']);
        });

        DB::statement('INSERT INTO activity_logs (log_name, description, subject_type, event, subject_id, causer_type, causer_id, created_at, updated_at)'.
            'SELECT "model_events", "created", "FluxErp\\\\Models\\\\Warehouse", "created", id, "FluxErp\\\\Models\\\\User", created_by, created_at, created_at FROM warehouses WHERE created_by IS NOT NULL');
        DB::statement('INSERT INTO activity_logs (log_name, description, subject_type, event, subject_id, causer_type, causer_id, created_at, updated_at)'.
            'SELECT "model_events", "updated", "FluxErp\\\\Models\\\\Warehouse", "updated", id, "FluxErp\\\\Models\\\\User", updated_by, updated_at, updated_at FROM warehouses WHERE updated_by IS NOT NULL');
        DB::statement('INSERT INTO activity_logs (log_name, description, subject_type, event, subject_id, causer_type, causer_id, created_at, updated_at)'.
            'SELECT "model_events", "deleted", "FluxErp\\\\Models\\\\Warehouse", "deleted", id, "FluxErp\\\\Models\\\\User", deleted_by, deleted_at, deleted_at FROM warehouses WHERE deleted_by IS NOT NULL');
        Schema::table('warehouses', function (Blueprint $table) {
            $table->dropForeign('warehouses_created_by_foreign');
            $table->dropForeign('warehouses_updated_by_foreign');
            $table->dropForeign('warehouses_deleted_by_foreign');
            $table->dropColumn(['created_by', 'updated_by', 'deleted_by']);
        });
    }

    public function down(): void
    {
        Schema::table('addresses', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that created this record.')
                ->after('created_at');
            $table->foreign('created_by')->references('id')->on('users');
            $table->unsignedBigInteger('updated_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that changed this record last.')
                ->after('updated_at');
            $table->foreign('updated_by')->references('id')->on('users');
            $table->unsignedBigInteger('deleted_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that deleted this record.')
                ->after('deleted_at');
            $table->foreign('deleted_by')->references('id')->on('users');
        });
        DB::statement(
            'UPDATE addresses SET
                created_by = (
                    SELECT causer_id
                    FROM activity_logs
                    WHERE subject_id = addresses.id
                      AND event = "created"
                      AND subject_type = "FluxErp\\\\Models\\\\Address"
                      AND causer_type = "FluxErp\\\\Models\\\\User"
                    ORDER BY id LIMIT 1
                )'
        );
        DB::statement(
            'UPDATE addresses SET
                updated_by = (
                    SELECT causer_id
                    FROM activity_logs
                    WHERE subject_id = addresses.id
                      AND event = "updated"
                      AND subject_type = "FluxErp\\\\Models\\\\Address"
                      AND causer_type = "FluxErp\\\\Models\\\\User"
                    ORDER BY id DESC LIMIT 1
                )'
        );
        DB::statement(
            'UPDATE addresses SET
                deleted_by = (
                    SELECT causer_id
                    FROM activity_logs
                    WHERE subject_id = addresses.id
                      AND event = "deleted"
                      AND subject_type = "FluxErp\\\\Models\\\\Address"
                      AND causer_type = "FluxErp\\\\Models\\\\User"
                    ORDER BY id DESC LIMIT 1
                )'
        );

        Schema::table('address_types', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that created this record.')
                ->after('created_at');
            $table->foreign('created_by')->references('id')->on('users');
            $table->unsignedBigInteger('updated_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that changed this record last.')
                ->after('updated_at');
            $table->foreign('updated_by')->references('id')->on('users');
            $table->unsignedBigInteger('deleted_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that deleted this record.')
                ->after('deleted_at');
            $table->foreign('deleted_by')->references('id')->on('users');
        });
        DB::statement(
            'UPDATE address_types SET
                created_by = (
                    SELECT causer_id
                    FROM activity_logs
                    WHERE subject_id = address_types.id
                      AND event = "created"
                      AND subject_type = "FluxErp\\\\Models\\\\AddressType"
                      AND causer_type = "FluxErp\\\\Models\\\\User"
                    ORDER BY id LIMIT 1
                )'
        );
        DB::statement(
            'UPDATE address_types SET
                updated_by = (
                    SELECT causer_id
                    FROM activity_logs
                    WHERE subject_id = address_types.id
                      AND event = "updated"
                      AND subject_type = "FluxErp\\\\Models\\\\AddressType"
                      AND causer_type = "FluxErp\\\\Models\\\\User"
                    ORDER BY id DESC LIMIT 1
                )'
        );
        DB::statement(
            'UPDATE address_types SET
                deleted_by = (
                    SELECT causer_id
                    FROM activity_logs
                    WHERE subject_id = address_types.id
                      AND event = "deleted"
                      AND subject_type = "FluxErp\\\\Models\\\\AddressType"
                      AND causer_type = "FluxErp\\\\Models\\\\User"
                    ORDER BY id DESC LIMIT 1
                )'
        );

        Schema::table('calendars', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that created this record.')
                ->after('created_at');
            $table->foreign('created_by')->references('id')->on('users');
            $table->unsignedBigInteger('updated_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that changed this record last.')
                ->after('updated_at');
            $table->foreign('updated_by')->references('id')->on('users');
        });
        DB::statement(
            'UPDATE calendars SET
                created_by = (
                    SELECT causer_id
                    FROM activity_logs
                    WHERE subject_id = calendars.id
                      AND event = "created"
                      AND subject_type = "FluxErp\\\\Models\\\\Calendar"
                      AND causer_type = "FluxErp\\\\Models\\\\User"
                    ORDER BY id LIMIT 1
                )'
        );
        DB::statement(
            'UPDATE calendars SET
                updated_by = (
                    SELECT causer_id
                    FROM activity_logs
                    WHERE subject_id = calendars.id
                      AND event = "updated"
                      AND subject_type = "FluxErp\\\\Models\\\\Calendar"
                      AND causer_type = "FluxErp\\\\Models\\\\User"
                    ORDER BY id DESC LIMIT 1
                )'
        );

        Schema::table('calendar_events', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that created this record.')
                ->after('created_at');
            $table->foreign('created_by')->references('id')->on('users');
            $table->unsignedBigInteger('updated_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that changed this record last.')
                ->after('updated_at');
            $table->foreign('updated_by')->references('id')->on('users');
        });
        DB::statement(
            'UPDATE calendar_events SET
                created_by = (
                    SELECT causer_id
                    FROM activity_logs
                    WHERE subject_id = calendar_events.id
                      AND event = "created"
                      AND subject_type = "FluxErp\\\\Models\\\\CalendarEvent"
                      AND causer_type = "FluxErp\\\\Models\\\\User"
                    ORDER BY id LIMIT 1
                )'
        );
        DB::statement(
            'UPDATE calendar_events SET
                updated_by = (
                    SELECT causer_id
                    FROM activity_logs
                    WHERE subject_id = calendar_events.id
                      AND event = "updated"
                      AND subject_type = "FluxErp\\\\Models\\\\CalendarEvent"
                      AND causer_type = "FluxErp\\\\Models\\\\User"
                    ORDER BY id DESC LIMIT 1
                )'
        );

        Schema::table('categories', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that created this record.')
                ->after('created_at');
            $table->foreign('created_by')->references('id')->on('users');
            $table->unsignedBigInteger('updated_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that changed this record last.')
                ->after('updated_at');
            $table->foreign('updated_by')->references('id')->on('users');
        });
        DB::statement(
            'UPDATE categories SET
                created_by = (
                    SELECT causer_id
                    FROM activity_logs
                    WHERE subject_id = categories.id
                      AND event = "created"
                      AND subject_type = "FluxErp\\\\Models\\\\Category"
                      AND causer_type = "FluxErp\\\\Models\\\\User"
                    ORDER BY id LIMIT 1
                )'
        );
        DB::statement(
            'UPDATE categories SET
                updated_by = (
                    SELECT causer_id
                    FROM activity_logs
                    WHERE subject_id = categories.id
                      AND event = "updated"
                      AND subject_type = "FluxErp\\\\Models\\\\Category"
                      AND causer_type = "FluxErp\\\\Models\\\\User"
                    ORDER BY id DESC LIMIT 1
                )'
        );

        Schema::table('clients', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that created this record.')
                ->after('created_at');
            $table->foreign('created_by')->references('id')->on('users');
            $table->unsignedBigInteger('updated_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that changed this record last.')
                ->after('updated_at');
            $table->foreign('updated_by')->references('id')->on('users');
            $table->unsignedBigInteger('deleted_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that deleted this record.')
                ->after('deleted_at');
            $table->foreign('deleted_by')->references('id')->on('users');
        });
        DB::statement(
            'UPDATE clients SET
                created_by = (
                    SELECT causer_id
                    FROM activity_logs
                    WHERE subject_id = clients.id
                      AND event = "created"
                      AND subject_type = "FluxErp\\\\Models\\\\Client"
                      AND causer_type = "FluxErp\\\\Models\\\\User"
                    ORDER BY id LIMIT 1
                )'
        );
        DB::statement(
            'UPDATE clients SET
                updated_by = (
                    SELECT causer_id
                    FROM activity_logs
                    WHERE subject_id = clients.id
                      AND event = "updated"
                      AND subject_type = "FluxErp\\\\Models\\\\Client"
                      AND causer_type = "FluxErp\\\\Models\\\\User"
                    ORDER BY id DESC LIMIT 1
                )'
        );
        DB::statement(
            'UPDATE clients SET
                deleted_by = (
                    SELECT causer_id
                    FROM activity_logs
                    WHERE subject_id = clients.id
                      AND event = "deleted"
                      AND subject_type = "FluxErp\\\\Models\\\\Client"
                      AND causer_type = "FluxErp\\\\Models\\\\User"
                    ORDER BY id DESC LIMIT 1
                )'
        );

        Schema::table('comments', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that created this record.')
                ->after('created_at');
            $table->foreign('created_by')->references('id')->on('users');
            $table->unsignedBigInteger('updated_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that changed this record last.')
                ->after('updated_at');
            $table->foreign('updated_by')->references('id')->on('users');
            $table->unsignedBigInteger('deleted_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that deleted this record.')
                ->after('deleted_at');
            $table->foreign('deleted_by')->references('id')->on('users');
        });
        DB::statement(
            'UPDATE comments SET
                created_by = (
                    SELECT causer_id
                    FROM activity_logs
                    WHERE subject_id = comments.id
                      AND event = "created"
                      AND subject_type = "FluxErp\\\\Models\\\\Comment"
                      AND causer_type = "FluxErp\\\\Models\\\\User"
                    ORDER BY id LIMIT 1
                )'
        );
        DB::statement(
            'UPDATE comments SET
                updated_by = (
                    SELECT causer_id
                    FROM activity_logs
                    WHERE subject_id = comments.id
                      AND event = "updated"
                      AND subject_type = "FluxErp\\\\Models\\\\Comment"
                      AND causer_type = "FluxErp\\\\Models\\\\User"
                    ORDER BY id DESC LIMIT 1
                )'
        );
        DB::statement(
            'UPDATE comments SET
                deleted_by = (
                    SELECT causer_id
                    FROM activity_logs
                    WHERE subject_id = comments.id
                      AND event = "deleted"
                      AND subject_type = "FluxErp\\\\Models\\\\Comment"
                      AND causer_type = "FluxErp\\\\Models\\\\User"
                    ORDER BY id DESC LIMIT 1
                )'
        );

        Schema::table('contacts', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that created this record.')
                ->after('created_at');
            $table->foreign('created_by')->references('id')->on('users');
            $table->unsignedBigInteger('updated_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that changed this record last.')
                ->after('updated_at');
            $table->foreign('updated_by')->references('id')->on('users');
            $table->unsignedBigInteger('deleted_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that deleted this record.')
                ->after('deleted_at');
            $table->foreign('deleted_by')->references('id')->on('users');
        });
        DB::statement(
            'UPDATE contacts SET
                created_by = (
                    SELECT causer_id
                    FROM activity_logs
                    WHERE subject_id = contacts.id
                      AND event = "created"
                      AND subject_type = "FluxErp\\\\Models\\\\Contact"
                      AND causer_type = "FluxErp\\\\Models\\\\User"
                    ORDER BY id LIMIT 1
                )'
        );
        DB::statement(
            'UPDATE contacts SET
                updated_by = (
                    SELECT causer_id
                    FROM activity_logs
                    WHERE subject_id = contacts.id
                      AND event = "updated"
                      AND subject_type = "FluxErp\\\\Models\\\\Contact"
                      AND causer_type = "FluxErp\\\\Models\\\\User"
                    ORDER BY id DESC LIMIT 1
                )'
        );
        DB::statement(
            'UPDATE contacts SET
                deleted_by = (
                    SELECT causer_id
                    FROM activity_logs
                    WHERE subject_id = contacts.id
                      AND event = "deleted"
                      AND subject_type = "FluxErp\\\\Models\\\\Contact"
                      AND causer_type = "FluxErp\\\\Models\\\\User"
                    ORDER BY id DESC LIMIT 1
                )'
        );

        Schema::table('contact_bank_connections', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that created this record.')
                ->after('created_at');
            $table->foreign('created_by')->references('id')->on('users');
            $table->unsignedBigInteger('updated_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that changed this record last.')
                ->after('updated_at');
            $table->foreign('updated_by')->references('id')->on('users');
            $table->unsignedBigInteger('deleted_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that deleted this record.')
                ->after('deleted_at');
            $table->foreign('deleted_by')->references('id')->on('users');
        });
        DB::statement(
            'UPDATE contact_bank_connections SET
                created_by = (
                    SELECT causer_id
                    FROM activity_logs
                    WHERE subject_id = contact_bank_connections.id
                      AND event = "created"
                      AND subject_type = "FluxErp\\\\Models\\\\ContactBankConnection"
                      AND causer_type = "FluxErp\\\\Models\\\\User"
                    ORDER BY id LIMIT 1
                )'
        );
        DB::statement(
            'UPDATE contact_bank_connections SET
                updated_by = (
                    SELECT causer_id
                    FROM activity_logs
                    WHERE subject_id = contact_bank_connections.id
                      AND event = "updated"
                      AND subject_type = "FluxErp\\\\Models\\\\ContactBankConnection"
                      AND causer_type = "FluxErp\\\\Models\\\\User"
                    ORDER BY id DESC LIMIT 1
                )'
        );
        DB::statement(
            'UPDATE contact_bank_connections SET
                deleted_by = (
                    SELECT causer_id
                    FROM activity_logs
                    WHERE subject_id = contact_bank_connections.id
                      AND event = "deleted"
                      AND subject_type = "FluxErp\\\\Models\\\\ContactBankConnection"
                      AND causer_type = "FluxErp\\\\Models\\\\User"
                    ORDER BY id DESC LIMIT 1
                )'
        );

        Schema::table('contact_options', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that created this record.')
                ->after('created_at');
            $table->foreign('created_by')->references('id')->on('users');
            $table->unsignedBigInteger('updated_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that changed this record last.')
                ->after('updated_at');
            $table->foreign('updated_by')->references('id')->on('users');
        });
        DB::statement(
            'UPDATE contact_options SET
                created_by = (
                    SELECT causer_id
                    FROM activity_logs
                    WHERE subject_id = contact_options.id
                      AND event = "created"
                      AND subject_type = "FluxErp\\\\Models\\\\ContactOption"
                      AND causer_type = "FluxErp\\\\Models\\\\User"
                    ORDER BY id LIMIT 1
                )'
        );
        DB::statement(
            'UPDATE contact_options SET
                updated_by = (
                    SELECT causer_id
                    FROM activity_logs
                    WHERE subject_id = contact_options.id
                      AND event = "updated"
                      AND subject_type = "FluxErp\\\\Models\\\\ContactOption"
                      AND causer_type = "FluxErp\\\\Models\\\\User"
                    ORDER BY id DESC LIMIT 1
                )'
        );

        Schema::table('countries', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that created this record.')
                ->after('created_at');
            $table->foreign('created_by')->references('id')->on('users');
            $table->unsignedBigInteger('updated_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that changed this record last.')
                ->after('updated_at');
            $table->foreign('updated_by')->references('id')->on('users');
            $table->unsignedBigInteger('deleted_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that deleted this record.')
                ->after('deleted_at');
            $table->foreign('deleted_by')->references('id')->on('users');
        });
        DB::statement(
            'UPDATE countries SET
                created_by = (
                    SELECT causer_id
                    FROM activity_logs
                    WHERE subject_id = countries.id
                      AND event = "created"
                      AND subject_type = "FluxErp\\\\Models\\\\Country"
                      AND causer_type = "FluxErp\\\\Models\\\\User"
                    ORDER BY id LIMIT 1
                )'
        );
        DB::statement(
            'UPDATE countries SET
                updated_by = (
                    SELECT causer_id
                    FROM activity_logs
                    WHERE subject_id = countries.id
                      AND event = "updated"
                      AND subject_type = "FluxErp\\\\Models\\\\Country"
                      AND causer_type = "FluxErp\\\\Models\\\\User"
                    ORDER BY id DESC LIMIT 1
                )'
        );
        DB::statement(
            'UPDATE countries SET
                deleted_by = (
                    SELECT causer_id
                    FROM activity_logs
                    WHERE subject_id = countries.id
                      AND event = "deleted"
                      AND subject_type = "FluxErp\\\\Models\\\\Country"
                      AND causer_type = "FluxErp\\\\Models\\\\User"
                    ORDER BY id DESC LIMIT 1
                )'
        );

        Schema::table('country_regions', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that created this record.')
                ->after('created_at');
            $table->foreign('created_by')->references('id')->on('users');
            $table->unsignedBigInteger('updated_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that changed this record last.')
                ->after('updated_at');
            $table->foreign('updated_by')->references('id')->on('users');
            $table->unsignedBigInteger('deleted_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that deleted this record.')
                ->after('deleted_at');
            $table->foreign('deleted_by')->references('id')->on('users');
        });
        DB::statement(
            'UPDATE country_regions SET
                created_by = (
                    SELECT causer_id
                    FROM activity_logs
                    WHERE subject_id = country_regions.id
                      AND event = "created"
                      AND subject_type = "FluxErp\\\\Models\\\\CountryRegion"
                      AND causer_type = "FluxErp\\\\Models\\\\User"
                    ORDER BY id LIMIT 1
                )'
        );
        DB::statement(
            'UPDATE country_regions SET
                updated_by = (
                    SELECT causer_id
                    FROM activity_logs
                    WHERE subject_id = country_regions.id
                      AND event = "updated"
                      AND subject_type = "FluxErp\\\\Models\\\\CountryRegion"
                      AND causer_type = "FluxErp\\\\Models\\\\User"
                    ORDER BY id DESC LIMIT 1
                )'
        );
        DB::statement(
            'UPDATE country_regions SET
                deleted_by = (
                    SELECT causer_id
                    FROM activity_logs
                    WHERE subject_id = country_regions.id
                      AND event = "deleted"
                      AND subject_type = "FluxErp\\\\Models\\\\CountryRegion"
                      AND causer_type = "FluxErp\\\\Models\\\\User"
                    ORDER BY id DESC LIMIT 1
                )'
        );

        Schema::table('currencies', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that created this record.')
                ->after('created_at');
            $table->foreign('created_by')->references('id')->on('users');
            $table->unsignedBigInteger('updated_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that changed this record last.')
                ->after('updated_at');
            $table->foreign('updated_by')->references('id')->on('users');
            $table->unsignedBigInteger('deleted_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that deleted this record.')
                ->after('deleted_at');
            $table->foreign('deleted_by')->references('id')->on('users');
        });
        DB::statement(
            'UPDATE currencies SET
                created_by = (
                    SELECT causer_id
                    FROM activity_logs
                    WHERE subject_id = currencies.id
                      AND event = "created"
                      AND subject_type = "FluxErp\\\\Models\\\\Currency"
                      AND causer_type = "FluxErp\\\\Models\\\\User"
                    ORDER BY id LIMIT 1
                )'
        );
        DB::statement(
            'UPDATE currencies SET
                updated_by = (
                    SELECT causer_id
                    FROM activity_logs
                    WHERE subject_id = currencies.id
                      AND event = "updated"
                      AND subject_type = "FluxErp\\\\Models\\\\Currency"
                      AND causer_type = "FluxErp\\\\Models\\\\User"
                    ORDER BY id DESC LIMIT 1
                )'
        );
        DB::statement(
            'UPDATE currencies SET
                deleted_by = (
                    SELECT causer_id
                    FROM activity_logs
                    WHERE subject_id = currencies.id
                      AND event = "deleted"
                      AND subject_type = "FluxErp\\\\Models\\\\Currency"
                      AND causer_type = "FluxErp\\\\Models\\\\User"
                    ORDER BY id DESC LIMIT 1
                )'
        );

        Schema::table('discounts', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that created this record.')
                ->after('created_at');
            $table->foreign('created_by')->references('id')->on('users');
            $table->unsignedBigInteger('updated_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that changed this record last.')
                ->after('updated_at');
            $table->foreign('updated_by')->references('id')->on('users');
            $table->unsignedBigInteger('deleted_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that deleted this record.')
                ->after('deleted_at');
            $table->foreign('deleted_by')->references('id')->on('users');
        });
        DB::statement(
            'UPDATE discounts SET
                created_by = (
                    SELECT causer_id
                    FROM activity_logs
                    WHERE subject_id = discounts.id
                      AND event = "created"
                      AND subject_type = "FluxErp\\\\Models\\\\Discount"
                      AND causer_type = "FluxErp\\\\Models\\\\User"
                    ORDER BY id LIMIT 1
                )'
        );
        DB::statement(
            'UPDATE discounts SET
                updated_by = (
                    SELECT causer_id
                    FROM activity_logs
                    WHERE subject_id = discounts.id
                      AND event = "updated"
                      AND subject_type = "FluxErp\\\\Models\\\\Discount"
                      AND causer_type = "FluxErp\\\\Models\\\\User"
                    ORDER BY id DESC LIMIT 1
                )'
        );
        DB::statement(
            'UPDATE discounts SET
                deleted_by = (
                    SELECT causer_id
                    FROM activity_logs
                    WHERE subject_id = discounts.id
                      AND event = "deleted"
                      AND subject_type = "FluxErp\\\\Models\\\\Discount"
                      AND causer_type = "FluxErp\\\\Models\\\\User"
                    ORDER BY id DESC LIMIT 1
                )'
        );

        Schema::table('document_generation_settings', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that created this record.')
                ->after('created_at');
            $table->foreign('created_by')->references('id')->on('users');
            $table->unsignedBigInteger('updated_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that changed this record last.')
                ->after('updated_at');
            $table->foreign('updated_by')->references('id')->on('users');
            $table->unsignedBigInteger('deleted_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that deleted this record.')
                ->after('deleted_at');
            $table->foreign('deleted_by')->references('id')->on('users');
        });
        DB::statement(
            'UPDATE document_generation_settings SET
                created_by = (
                    SELECT causer_id
                    FROM activity_logs
                    WHERE subject_id = document_generation_settings.id
                      AND event = "created"
                      AND subject_type = "FluxErp\\\\Models\\\\DocumentGenerationSetting"
                      AND causer_type = "FluxErp\\\\Models\\\\User"
                    ORDER BY id LIMIT 1
                )'
        );
        DB::statement(
            'UPDATE document_generation_settings SET
                updated_by = (
                    SELECT causer_id
                    FROM activity_logs
                    WHERE subject_id = document_generation_settings.id
                      AND event = "updated"
                      AND subject_type = "FluxErp\\\\Models\\\\DocumentGenerationSetting"
                      AND causer_type = "FluxErp\\\\Models\\\\User"
                    ORDER BY id DESC LIMIT 1
                )'
        );
        DB::statement(
            'UPDATE document_generation_settings SET
                deleted_by = (
                    SELECT causer_id
                    FROM activity_logs
                    WHERE subject_id = document_generation_settings.id
                      AND event = "deleted"
                      AND subject_type = "FluxErp\\\\Models\\\\DocumentGenerationSetting"
                      AND causer_type = "FluxErp\\\\Models\\\\User"
                    ORDER BY id DESC LIMIT 1
                )'
        );

        Schema::table('document_types', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that created this record.')
                ->after('created_at');
            $table->foreign('created_by')->references('id')->on('users');
            $table->unsignedBigInteger('updated_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that changed this record last.')
                ->after('updated_at');
            $table->foreign('updated_by')->references('id')->on('users');
            $table->unsignedBigInteger('deleted_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that deleted this record.')
                ->after('deleted_at');
            $table->foreign('deleted_by')->references('id')->on('users');
        });
        DB::statement(
            'UPDATE document_types SET
                created_by = (
                    SELECT causer_id
                    FROM activity_logs
                    WHERE subject_id = document_types.id
                      AND event = "created"
                      AND subject_type = "FluxErp\\\\Models\\\\DocumentType"
                      AND causer_type = "FluxErp\\\\Models\\\\User"
                    ORDER BY id LIMIT 1
                )'
        );
        DB::statement(
            'UPDATE document_types SET
                updated_by = (
                    SELECT causer_id
                    FROM activity_logs
                    WHERE subject_id = document_types.id
                      AND event = "updated"
                      AND subject_type = "FluxErp\\\\Models\\\\DocumentType"
                      AND causer_type = "FluxErp\\\\Models\\\\User"
                    ORDER BY id DESC LIMIT 1
                )'
        );
        DB::statement(
            'UPDATE document_types SET
                deleted_by = (
                    SELECT causer_id
                    FROM activity_logs
                    WHERE subject_id = document_types.id
                      AND event = "deleted"
                      AND subject_type = "FluxErp\\\\Models\\\\DocumentType"
                      AND causer_type = "FluxErp\\\\Models\\\\User"
                    ORDER BY id DESC LIMIT 1
                )'
        );

        Schema::table('emails', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that created this record.')
                ->after('created_at');
            $table->foreign('created_by')->references('id')->on('users');
            $table->unsignedBigInteger('updated_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that changed this record last.')
                ->after('updated_at');
            $table->foreign('updated_by')->references('id')->on('users');
            $table->unsignedBigInteger('deleted_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that deleted this record.')
                ->after('deleted_at');
            $table->foreign('deleted_by')->references('id')->on('users');
        });
        DB::statement(
            'UPDATE emails SET
                created_by = (
                    SELECT causer_id
                    FROM activity_logs
                    WHERE subject_id = emails.id
                      AND event = "created"
                      AND subject_type = "FluxErp\\\\Models\\\\Email"
                      AND causer_type = "FluxErp\\\\Models\\\\User"
                    ORDER BY id LIMIT 1
                )'
        );
        DB::statement(
            'UPDATE emails SET
                updated_by = (
                    SELECT causer_id
                    FROM activity_logs
                    WHERE subject_id = emails.id
                      AND event = "updated"
                      AND subject_type = "FluxErp\\\\Models\\\\Email"
                      AND causer_type = "FluxErp\\\\Models\\\\User"
                    ORDER BY id DESC LIMIT 1
                )'
        );
        DB::statement(
            'UPDATE emails SET
                deleted_by = (
                    SELECT causer_id
                    FROM activity_logs
                    WHERE subject_id = emails.id
                      AND event = "deleted"
                      AND subject_type = "FluxErp\\\\Models\\\\Email"
                      AND causer_type = "FluxErp\\\\Models\\\\User"
                    ORDER BY id DESC LIMIT 1
                )'
        );

        Schema::table('email_templates', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that created this record.')
                ->after('created_at');
            $table->foreign('created_by')->references('id')->on('users');
            $table->unsignedBigInteger('updated_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that changed this record last.')
                ->after('updated_at');
            $table->foreign('updated_by')->references('id')->on('users');
            $table->unsignedBigInteger('deleted_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that deleted this record.')
                ->after('deleted_at');
            $table->foreign('deleted_by')->references('id')->on('users');
        });
        DB::statement(
            'UPDATE email_templates SET
                created_by = (
                    SELECT causer_id
                    FROM activity_logs
                    WHERE subject_id = email_templates.id
                      AND event = "created"
                      AND subject_type = "FluxErp\\\\Models\\\\EmailTemplate"
                      AND causer_type = "FluxErp\\\\Models\\\\User"
                    ORDER BY id LIMIT 1
                )'
        );
        DB::statement(
            'UPDATE email_templates SET
                updated_by = (
                    SELECT causer_id
                    FROM activity_logs
                    WHERE subject_id = email_templates.id
                      AND event = "updated"
                      AND subject_type = "FluxErp\\\\Models\\\\EmailTemplate"
                      AND causer_type = "FluxErp\\\\Models\\\\User"
                    ORDER BY id DESC LIMIT 1
                )'
        );
        DB::statement(
            'UPDATE email_templates SET
                deleted_by = (
                    SELECT causer_id
                    FROM activity_logs
                    WHERE subject_id = email_templates.id
                      AND event = "deleted"
                      AND subject_type = "FluxErp\\\\Models\\\\EmailTemplate"
                      AND causer_type = "FluxErp\\\\Models\\\\User"
                    ORDER BY id DESC LIMIT 1
                )'
        );

        Schema::table('interface_users', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that created this record.')
                ->after('created_at');
            $table->foreign('created_by')->references('id')->on('users');
            $table->unsignedBigInteger('updated_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that changed this record last.')
                ->after('updated_at');
            $table->foreign('updated_by')->references('id')->on('users');
            $table->unsignedBigInteger('deleted_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that deleted this record.')
                ->after('deleted_at');
            $table->foreign('deleted_by')->references('id')->on('users');
        });
        DB::statement(
            'UPDATE interface_users SET
                created_by = (
                    SELECT causer_id
                    FROM activity_logs
                    WHERE subject_id = interface_users.id
                      AND event = "created"
                      AND subject_type = "FluxErp\\\\Models\\\\InterfaceUser"
                      AND causer_type = "FluxErp\\\\Models\\\\User"
                    ORDER BY id LIMIT 1
                )'
        );
        DB::statement(
            'UPDATE interface_users SET
                updated_by = (
                    SELECT causer_id
                    FROM activity_logs
                    WHERE subject_id = interface_users.id
                      AND event = "updated"
                      AND subject_type = "FluxErp\\\\Models\\\\InterfaceUser"
                      AND causer_type = "FluxErp\\\\Models\\\\User"
                    ORDER BY id DESC LIMIT 1
                )'
        );
        DB::statement(
            'UPDATE interface_users SET
                deleted_by = (
                    SELECT causer_id
                    FROM activity_logs
                    WHERE subject_id = interface_users.id
                      AND event = "deleted"
                      AND subject_type = "FluxErp\\\\Models\\\\InterfaceUser"
                      AND causer_type = "FluxErp\\\\Models\\\\User"
                    ORDER BY id DESC LIMIT 1
                )'
        );

        Schema::table('languages', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that created this record.')
                ->after('created_at');
            $table->foreign('created_by')->references('id')->on('users');
            $table->unsignedBigInteger('updated_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that changed this record last.')
                ->after('updated_at');
            $table->foreign('updated_by')->references('id')->on('users');
            $table->unsignedBigInteger('deleted_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that deleted this record.')
                ->after('deleted_at');
            $table->foreign('deleted_by')->references('id')->on('users');
        });
        DB::statement(
            'UPDATE languages SET
                created_by = (
                    SELECT causer_id
                    FROM activity_logs
                    WHERE subject_id = languages.id
                      AND event = "created"
                      AND subject_type = "FluxErp\\\\Models\\\\Language"
                      AND causer_type = "FluxErp\\\\Models\\\\User"
                    ORDER BY id LIMIT 1
                )'
        );
        DB::statement(
            'UPDATE languages SET
                updated_by = (
                    SELECT causer_id
                    FROM activity_logs
                    WHERE subject_id = languages.id
                      AND event = "updated"
                      AND subject_type = "FluxErp\\\\Models\\\\Language"
                      AND causer_type = "FluxErp\\\\Models\\\\User"
                    ORDER BY id DESC LIMIT 1
                )'
        );
        DB::statement(
            'UPDATE languages SET
                deleted_by = (
                    SELECT causer_id
                    FROM activity_logs
                    WHERE subject_id = languages.id
                      AND event = "deleted"
                      AND subject_type = "FluxErp\\\\Models\\\\Language"
                      AND causer_type = "FluxErp\\\\Models\\\\User"
                    ORDER BY id DESC LIMIT 1
                )'
        );

        Schema::table('locks', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that created this record.')
                ->after('created_at');
            $table->foreign('created_by')->references('id')->on('users');
            $table->unsignedBigInteger('updated_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that changed this record last.')
                ->after('updated_at');
            $table->foreign('updated_by')->references('id')->on('users');
        });
        DB::statement(
            'UPDATE locks SET
                created_by = (
                    SELECT causer_id
                    FROM activity_logs
                    WHERE subject_id = locks.id
                      AND event = "created"
                      AND subject_type = "FluxErp\\\\Models\\\\Lock"
                      AND causer_type = "FluxErp\\\\Models\\\\User"
                    ORDER BY id LIMIT 1
                )'
        );
        DB::statement(
            'UPDATE locks SET
                updated_by = (
                    SELECT causer_id
                    FROM activity_logs
                    WHERE subject_id = locks.id
                      AND event = "updated"
                      AND subject_type = "FluxErp\\\\Models\\\\Lock"
                      AND causer_type = "FluxErp\\\\Models\\\\User"
                    ORDER BY id DESC LIMIT 1
                )'
        );

        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that created this record.')
                ->after('created_at');
            $table->foreign('created_by')->references('id')->on('users');
            $table->unsignedBigInteger('updated_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that changed this record last.')
                ->after('updated_at');
            $table->foreign('updated_by')->references('id')->on('users');
            $table->unsignedBigInteger('deleted_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that deleted this record.')
                ->after('deleted_at');
            $table->foreign('deleted_by')->references('id')->on('users');
        });
        DB::statement(
            'UPDATE orders SET
                created_by = (
                    SELECT causer_id
                    FROM activity_logs
                    WHERE subject_id = orders.id
                      AND event = "created"
                      AND subject_type = "FluxErp\\\\Models\\\\Order"
                      AND causer_type = "FluxErp\\\\Models\\\\User"
                    ORDER BY id LIMIT 1
                )'
        );
        DB::statement(
            'UPDATE orders SET
                updated_by = (
                    SELECT causer_id
                    FROM activity_logs
                    WHERE subject_id = orders.id
                      AND event = "updated"
                      AND subject_type = "FluxErp\\\\Models\\\\Order"
                      AND causer_type = "FluxErp\\\\Models\\\\User"
                    ORDER BY id DESC LIMIT 1
                )'
        );
        DB::statement(
            'UPDATE orders SET
                deleted_by = (
                    SELECT causer_id
                    FROM activity_logs
                    WHERE subject_id = orders.id
                      AND event = "deleted"
                      AND subject_type = "FluxErp\\\\Models\\\\Order"
                      AND causer_type = "FluxErp\\\\Models\\\\User"
                    ORDER BY id DESC LIMIT 1
                )'
        );

        Schema::table('order_positions', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that created this record.')
                ->after('created_at');
            $table->foreign('created_by')->references('id')->on('users');
            $table->unsignedBigInteger('updated_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that changed this record last.')
                ->after('updated_at');
            $table->foreign('updated_by')->references('id')->on('users');
            $table->unsignedBigInteger('deleted_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that deleted this record.')
                ->after('deleted_at');
            $table->foreign('deleted_by')->references('id')->on('users');
        });
        DB::statement(
            'UPDATE order_positions SET
                created_by = (
                    SELECT causer_id
                    FROM activity_logs
                    WHERE subject_id = order_positions.id
                      AND event = "created"
                      AND subject_type = "FluxErp\\\\Models\\\\OrderPosition"
                      AND causer_type = "FluxErp\\\\Models\\\\User"
                    ORDER BY id LIMIT 1
                )'
        );
        DB::statement(
            'UPDATE order_positions SET
                updated_by = (
                    SELECT causer_id
                    FROM activity_logs
                    WHERE subject_id = order_positions.id
                      AND event = "updated"
                      AND subject_type = "FluxErp\\\\Models\\\\OrderPosition"
                      AND causer_type = "FluxErp\\\\Models\\\\User"
                    ORDER BY id DESC LIMIT 1
                )'
        );
        DB::statement(
            'UPDATE order_positions SET
                deleted_by = (
                    SELECT causer_id
                    FROM activity_logs
                    WHERE subject_id = order_positions.id
                      AND event = "deleted"
                      AND subject_type = "FluxErp\\\\Models\\\\OrderPosition"
                      AND causer_type = "FluxErp\\\\Models\\\\User"
                    ORDER BY id DESC LIMIT 1
                )'
        );

        Schema::table('order_types', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that created this record.')
                ->after('created_at');
            $table->foreign('created_by')->references('id')->on('users');
            $table->unsignedBigInteger('updated_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that changed this record last.')
                ->after('updated_at');
            $table->foreign('updated_by')->references('id')->on('users');
            $table->unsignedBigInteger('deleted_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that deleted this record.')
                ->after('deleted_at');
            $table->foreign('deleted_by')->references('id')->on('users');
        });
        DB::statement(
            'UPDATE order_types SET
                created_by = (
                    SELECT causer_id
                    FROM activity_logs
                    WHERE subject_id = order_types.id
                      AND event = "created"
                      AND subject_type = "FluxErp\\\\Models\\\\OrderType"
                      AND causer_type = "FluxErp\\\\Models\\\\User"
                    ORDER BY id LIMIT 1
                )'
        );
        DB::statement(
            'UPDATE order_types SET
                updated_by = (
                    SELECT causer_id
                    FROM activity_logs
                    WHERE subject_id = order_types.id
                      AND event = "updated"
                      AND subject_type = "FluxErp\\\\Models\\\\OrderType"
                      AND causer_type = "FluxErp\\\\Models\\\\User"
                    ORDER BY id DESC LIMIT 1
                )'
        );
        DB::statement(
            'UPDATE order_types SET
                deleted_by = (
                    SELECT causer_id
                    FROM activity_logs
                    WHERE subject_id = order_types.id
                      AND event = "deleted"
                      AND subject_type = "FluxErp\\\\Models\\\\OrderType"
                      AND causer_type = "FluxErp\\\\Models\\\\User"
                    ORDER BY id DESC LIMIT 1
                )'
        );

        Schema::table('payment_notices', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that created this record.')
                ->after('created_at');
            $table->foreign('created_by')->references('id')->on('users');
            $table->unsignedBigInteger('updated_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that changed this record last.')
                ->after('updated_at');
            $table->foreign('updated_by')->references('id')->on('users');
            $table->unsignedBigInteger('deleted_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that deleted this record.')
                ->after('deleted_at');
            $table->foreign('deleted_by')->references('id')->on('users');
        });
        DB::statement(
            'UPDATE payment_notices SET
                created_by = (
                    SELECT causer_id
                    FROM activity_logs
                    WHERE subject_id = payment_notices.id
                      AND event = "created"
                      AND subject_type = "FluxErp\\\\Models\\\\PaymentNotice"
                      AND causer_type = "FluxErp\\\\Models\\\\User"
                    ORDER BY id LIMIT 1
                )'
        );
        DB::statement(
            'UPDATE payment_notices SET
                updated_by = (
                    SELECT causer_id
                    FROM activity_logs
                    WHERE subject_id = payment_notices.id
                      AND event = "updated"
                      AND subject_type = "FluxErp\\\\Models\\\\PaymentNotice"
                      AND causer_type = "FluxErp\\\\Models\\\\User"
                    ORDER BY id DESC LIMIT 1
                )'
        );
        DB::statement(
            'UPDATE payment_notices SET
                deleted_by = (
                    SELECT causer_id
                    FROM activity_logs
                    WHERE subject_id = payment_notices.id
                      AND event = "deleted"
                      AND subject_type = "FluxErp\\\\Models\\\\PaymentNotice"
                      AND causer_type = "FluxErp\\\\Models\\\\User"
                    ORDER BY id DESC LIMIT 1
                )'
        );

        Schema::table('payment_types', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that created this record.')
                ->after('created_at');
            $table->foreign('created_by')->references('id')->on('users');
            $table->unsignedBigInteger('updated_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that changed this record last.')
                ->after('updated_at');
            $table->foreign('updated_by')->references('id')->on('users');
            $table->unsignedBigInteger('deleted_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that deleted this record.')
                ->after('deleted_at');
            $table->foreign('deleted_by')->references('id')->on('users');
        });
        DB::statement(
            'UPDATE payment_types SET
                created_by = (
                    SELECT causer_id
                    FROM activity_logs
                    WHERE subject_id = payment_types.id
                      AND event = "created"
                      AND subject_type = "FluxErp\\\\Models\\\\PaymentType"
                      AND causer_type = "FluxErp\\\\Models\\\\User"
                    ORDER BY id LIMIT 1
                )'
        );
        DB::statement(
            'UPDATE payment_types SET
                updated_by = (
                    SELECT causer_id
                    FROM activity_logs
                    WHERE subject_id = payment_types.id
                      AND event = "updated"
                      AND subject_type = "FluxErp\\\\Models\\\\PaymentType"
                      AND causer_type = "FluxErp\\\\Models\\\\User"
                    ORDER BY id DESC LIMIT 1
                )'
        );
        DB::statement(
            'UPDATE payment_types SET
                deleted_by = (
                    SELECT causer_id
                    FROM activity_logs
                    WHERE subject_id = payment_types.id
                      AND event = "deleted"
                      AND subject_type = "FluxErp\\\\Models\\\\PaymentType"
                      AND causer_type = "FluxErp\\\\Models\\\\User"
                    ORDER BY id DESC LIMIT 1
                )'
        );

        Schema::table('presentations', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that created this record.')
                ->after('created_at');
            $table->foreign('created_by')->references('id')->on('users');
            $table->unsignedBigInteger('updated_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that changed this record last.')
                ->after('updated_at');
            $table->foreign('updated_by')->references('id')->on('users');
            $table->unsignedBigInteger('deleted_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that deleted this record.')
                ->after('deleted_at');
            $table->foreign('deleted_by')->references('id')->on('users');
        });
        DB::statement(
            'UPDATE presentations SET
                created_by = (
                    SELECT causer_id
                    FROM activity_logs
                    WHERE subject_id = presentations.id
                      AND event = "created"
                      AND subject_type = "FluxErp\\\\Models\\\\Presentation"
                      AND causer_type = "FluxErp\\\\Models\\\\User"
                    ORDER BY id LIMIT 1
                )'
        );
        DB::statement(
            'UPDATE presentations SET
                updated_by = (
                    SELECT causer_id
                    FROM activity_logs
                    WHERE subject_id = presentations.id
                      AND event = "updated"
                      AND subject_type = "FluxErp\\\\Models\\\\Presentation"
                      AND causer_type = "FluxErp\\\\Models\\\\User"
                    ORDER BY id DESC LIMIT 1
                )'
        );
        DB::statement(
            'UPDATE presentations SET
                deleted_by = (
                    SELECT causer_id
                    FROM activity_logs
                    WHERE subject_id = presentations.id
                      AND event = "deleted"
                      AND subject_type = "FluxErp\\\\Models\\\\Presentation"
                      AND causer_type = "FluxErp\\\\Models\\\\User"
                    ORDER BY id DESC LIMIT 1
                )'
        );

        Schema::table('prices', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that created this record.')
                ->after('created_at');
            $table->foreign('created_by')->references('id')->on('users');
            $table->unsignedBigInteger('updated_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that changed this record last.')
                ->after('updated_at');
            $table->foreign('updated_by')->references('id')->on('users');
            $table->unsignedBigInteger('deleted_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that deleted this record.')
                ->after('deleted_at');
            $table->foreign('deleted_by')->references('id')->on('users');
        });
        DB::statement(
            'UPDATE prices SET
                created_by = (
                    SELECT causer_id
                    FROM activity_logs
                    WHERE subject_id = prices.id
                      AND event = "created"
                      AND subject_type = "FluxErp\\\\Models\\\\Price"
                      AND causer_type = "FluxErp\\\\Models\\\\User"
                    ORDER BY id LIMIT 1
                )'
        );
        DB::statement(
            'UPDATE prices SET
                updated_by = (
                    SELECT causer_id
                    FROM activity_logs
                    WHERE subject_id = prices.id
                      AND event = "updated"
                      AND subject_type = "FluxErp\\\\Models\\\\Price"
                      AND causer_type = "FluxErp\\\\Models\\\\User"
                    ORDER BY id DESC LIMIT 1
                )'
        );
        DB::statement(
            'UPDATE prices SET
                deleted_by = (
                    SELECT causer_id
                    FROM activity_logs
                    WHERE subject_id = prices.id
                      AND event = "deleted"
                      AND subject_type = "FluxErp\\\\Models\\\\Price"
                      AND causer_type = "FluxErp\\\\Models\\\\User"
                    ORDER BY id DESC LIMIT 1
                )'
        );

        Schema::table('price_lists', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that created this record.')
                ->after('created_at');
            $table->foreign('created_by')->references('id')->on('users');
            $table->unsignedBigInteger('updated_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that changed this record last.')
                ->after('updated_at');
            $table->foreign('updated_by')->references('id')->on('users');
            $table->unsignedBigInteger('deleted_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that deleted this record.')
                ->after('deleted_at');
            $table->foreign('deleted_by')->references('id')->on('users');
        });
        DB::statement(
            'UPDATE price_lists SET
                created_by = (
                    SELECT causer_id
                    FROM activity_logs
                    WHERE subject_id = price_lists.id
                      AND event = "created"
                      AND subject_type = "FluxErp\\\\Models\\\\PriceList"
                      AND causer_type = "FluxErp\\\\Models\\\\User"
                    ORDER BY id LIMIT 1
                )'
        );
        DB::statement(
            'UPDATE price_lists SET
                updated_by = (
                    SELECT causer_id
                    FROM activity_logs
                    WHERE subject_id = price_lists.id
                      AND event = "updated"
                      AND subject_type = "FluxErp\\\\Models\\\\PriceList"
                      AND causer_type = "FluxErp\\\\Models\\\\User"
                    ORDER BY id DESC LIMIT 1
                )'
        );
        DB::statement(
            'UPDATE price_lists SET
                deleted_by = (
                    SELECT causer_id
                    FROM activity_logs
                    WHERE subject_id = price_lists.id
                      AND event = "deleted"
                      AND subject_type = "FluxErp\\\\Models\\\\PriceList"
                      AND causer_type = "FluxErp\\\\Models\\\\User"
                    ORDER BY id DESC LIMIT 1
                )'
        );

        Schema::table('print_data', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that created this record.')
                ->after('updated_at');
            $table->foreign('created_by')->references('id')->on('users');
            $table->unsignedBigInteger('updated_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that changed this record last.')
                ->after('created_by');
            $table->foreign('updated_by')->references('id')->on('users');
        });
        DB::statement(
            'UPDATE print_data SET
                created_by = (
                    SELECT causer_id
                    FROM activity_logs
                    WHERE subject_id = print_data.id
                      AND event = "created"
                      AND subject_type = "FluxErp\\\\Models\\\\PrintData"
                      AND causer_type = "FluxErp\\\\Models\\\\User"
                    ORDER BY id LIMIT 1
                )'
        );
        DB::statement(
            'UPDATE print_data SET
                updated_by = (
                    SELECT causer_id
                    FROM activity_logs
                    WHERE subject_id = print_data.id
                      AND event = "updated"
                      AND subject_type = "FluxErp\\\\Models\\\\PrintData"
                      AND causer_type = "FluxErp\\\\Models\\\\User"
                    ORDER BY id DESC LIMIT 1
                )'
        );

        Schema::table('products', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that created this record.')
                ->after('created_at');
            $table->foreign('created_by')->references('id')->on('users');
            $table->unsignedBigInteger('updated_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that changed this record last.')
                ->after('updated_at');
            $table->foreign('updated_by')->references('id')->on('users');
            $table->unsignedBigInteger('deleted_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that deleted this record.')
                ->after('deleted_at');
            $table->foreign('deleted_by')->references('id')->on('users');
        });
        DB::statement(
            'UPDATE products SET
                created_by = (
                    SELECT causer_id
                    FROM activity_logs
                    WHERE subject_id = products.id
                      AND event = "created"
                      AND subject_type = "FluxErp\\\\Models\\\\Product"
                      AND causer_type = "FluxErp\\\\Models\\\\User"
                    ORDER BY id LIMIT 1
                )'
        );
        DB::statement(
            'UPDATE products SET
                updated_by = (
                    SELECT causer_id
                    FROM activity_logs
                    WHERE subject_id = products.id
                      AND event = "updated"
                      AND subject_type = "FluxErp\\\\Models\\\\Product"
                      AND causer_type = "FluxErp\\\\Models\\\\User"
                    ORDER BY id DESC LIMIT 1
                )'
        );
        DB::statement(
            'UPDATE products SET
                deleted_by = (
                    SELECT causer_id
                    FROM activity_logs
                    WHERE subject_id = products.id
                      AND event = "deleted"
                      AND subject_type = "FluxErp\\\\Models\\\\Product"
                      AND causer_type = "FluxErp\\\\Models\\\\User"
                    ORDER BY id DESC LIMIT 1
                )'
        );

        Schema::table('product_options', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that created this record.')
                ->after('created_at');
            $table->foreign('created_by')->references('id')->on('users');
            $table->unsignedBigInteger('updated_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that changed this record last.')
                ->after('updated_at');
            $table->foreign('updated_by')->references('id')->on('users');
            $table->unsignedBigInteger('deleted_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that deleted this record.')
                ->after('deleted_at');
            $table->foreign('deleted_by')->references('id')->on('users');
        });
        DB::statement(
            'UPDATE product_options SET
                created_by = (
                    SELECT causer_id
                    FROM activity_logs
                    WHERE subject_id = product_options.id
                      AND event = "created"
                      AND subject_type = "FluxErp\\\\Models\\\\ProductOption"
                      AND causer_type = "FluxErp\\\\Models\\\\User"
                    ORDER BY id LIMIT 1
                )'
        );
        DB::statement(
            'UPDATE product_options SET
                updated_by = (
                    SELECT causer_id
                    FROM activity_logs
                    WHERE subject_id = product_options.id
                      AND event = "updated"
                      AND subject_type = "FluxErp\\\\Models\\\\ProductOption"
                      AND causer_type = "FluxErp\\\\Models\\\\User"
                    ORDER BY id DESC LIMIT 1
                )'
        );
        DB::statement(
            'UPDATE product_options SET
                deleted_by = (
                    SELECT causer_id
                    FROM activity_logs
                    WHERE subject_id = product_options.id
                      AND event = "deleted"
                      AND subject_type = "FluxErp\\\\Models\\\\ProductOption"
                      AND causer_type = "FluxErp\\\\Models\\\\User"
                    ORDER BY id DESC LIMIT 1
                )'
        );

        Schema::table('product_option_groups', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that created this record.')
                ->after('created_at');
            $table->foreign('created_by')->references('id')->on('users');
            $table->unsignedBigInteger('updated_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that changed this record last.')
                ->after('updated_at');
            $table->foreign('updated_by')->references('id')->on('users');
            $table->unsignedBigInteger('deleted_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that deleted this record.')
                ->after('deleted_at');
            $table->foreign('deleted_by')->references('id')->on('users');
        });
        DB::statement(
            'UPDATE product_option_groups SET
                created_by = (
                    SELECT causer_id
                    FROM activity_logs
                    WHERE subject_id = product_option_groups.id
                      AND event = "created"
                      AND subject_type = "FluxErp\\\\Models\\\\ProductOptionGroup"
                      AND causer_type = "FluxErp\\\\Models\\\\User"
                    ORDER BY id LIMIT 1
                )'
        );
        DB::statement(
            'UPDATE product_option_groups SET
                updated_by = (
                    SELECT causer_id
                    FROM activity_logs
                    WHERE subject_id = product_option_groups.id
                      AND event = "updated"
                      AND subject_type = "FluxErp\\\\Models\\\\ProductOptionGroup"
                      AND causer_type = "FluxErp\\\\Models\\\\User"
                    ORDER BY id DESC LIMIT 1
                )'
        );
        DB::statement(
            'UPDATE product_option_groups SET
                deleted_by = (
                    SELECT causer_id
                    FROM activity_logs
                    WHERE subject_id = product_option_groups.id
                      AND event = "deleted"
                      AND subject_type = "FluxErp\\\\Models\\\\ProductOptionGroup"
                      AND causer_type = "FluxErp\\\\Models\\\\User"
                    ORDER BY id DESC LIMIT 1
                )'
        );

        Schema::table('product_properties', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that created this record.')
                ->after('created_at');
            $table->foreign('created_by')->references('id')->on('users');
            $table->unsignedBigInteger('updated_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that changed this record last.')
                ->after('updated_at');
            $table->foreign('updated_by')->references('id')->on('users');
            $table->unsignedBigInteger('deleted_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that deleted this record.')
                ->after('deleted_at');
            $table->foreign('deleted_by')->references('id')->on('users');
        });
        DB::statement(
            'UPDATE product_properties SET
                created_by = (
                    SELECT causer_id
                    FROM activity_logs
                    WHERE subject_id = product_properties.id
                      AND event = "created"
                      AND subject_type = "FluxErp\\\\Models\\\\ProductProperty"
                      AND causer_type = "FluxErp\\\\Models\\\\User"
                    ORDER BY id LIMIT 1
                )'
        );
        DB::statement(
            'UPDATE product_properties SET
                updated_by = (
                    SELECT causer_id
                    FROM activity_logs
                    WHERE subject_id = product_properties.id
                      AND event = "updated"
                      AND subject_type = "FluxErp\\\\Models\\\\ProductProperty"
                      AND causer_type = "FluxErp\\\\Models\\\\User"
                    ORDER BY id DESC LIMIT 1
                )'
        );
        DB::statement(
            'UPDATE product_properties SET
                deleted_by = (
                    SELECT causer_id
                    FROM activity_logs
                    WHERE subject_id = product_properties.id
                      AND event = "deleted"
                      AND subject_type = "FluxErp\\\\Models\\\\ProductProperty"
                      AND causer_type = "FluxErp\\\\Models\\\\User"
                    ORDER BY id DESC LIMIT 1
                )'
        );

        Schema::table('projects', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that created this record.')
                ->after('created_at');
            $table->foreign('created_by')->references('id')->on('users');
            $table->unsignedBigInteger('updated_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that changed this record last.')
                ->after('updated_at');
            $table->foreign('updated_by')->references('id')->on('users');
            $table->unsignedBigInteger('deleted_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that deleted this record.')
                ->after('deleted_at');
            $table->foreign('deleted_by')->references('id')->on('users');
        });
        DB::statement(
            'UPDATE projects SET
                created_by = (
                    SELECT causer_id
                    FROM activity_logs
                    WHERE subject_id = projects.id
                      AND event = "created"
                      AND subject_type = "FluxErp\\\\Models\\\\Project"
                      AND causer_type = "FluxErp\\\\Models\\\\User"
                    ORDER BY id LIMIT 1
                )'
        );
        DB::statement(
            'UPDATE projects SET
                updated_by = (
                    SELECT causer_id
                    FROM activity_logs
                    WHERE subject_id = projects.id
                      AND event = "updated"
                      AND subject_type = "FluxErp\\\\Models\\\\Project"
                      AND causer_type = "FluxErp\\\\Models\\\\User"
                    ORDER BY id DESC LIMIT 1
                )'
        );
        DB::statement(
            'UPDATE projects SET
                deleted_by = (
                    SELECT causer_id
                    FROM activity_logs
                    WHERE subject_id = projects.id
                      AND event = "deleted"
                      AND subject_type = "FluxErp\\\\Models\\\\Project"
                      AND causer_type = "FluxErp\\\\Models\\\\User"
                    ORDER BY id DESC LIMIT 1
                )'
        );

        Schema::table('project_category_templates', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that created this record.')
                ->after('created_at');
            $table->foreign('created_by')->references('id')->on('users');
            $table->unsignedBigInteger('updated_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that changed this record last.')
                ->after('updated_at');
            $table->foreign('updated_by')->references('id')->on('users');
        });
        DB::statement(
            'UPDATE project_category_templates SET
                created_by = (
                    SELECT causer_id
                    FROM activity_logs
                    WHERE subject_id = project_category_templates.id
                      AND event = "created"
                      AND subject_type = "FluxErp\\\\Models\\\\ProjectCategoryTemplate"
                      AND causer_type = "FluxErp\\\\Models\\\\User"
                    ORDER BY id LIMIT 1
                )'
        );
        DB::statement(
            'UPDATE project_category_templates SET
                updated_by = (
                    SELECT causer_id
                    FROM activity_logs
                    WHERE subject_id = project_category_templates.id
                      AND event = "updated"
                      AND subject_type = "FluxErp\\\\Models\\\\ProjectCategoryTemplate"
                      AND causer_type = "FluxErp\\\\Models\\\\User"
                    ORDER BY id DESC LIMIT 1
                )'
        );

        Schema::table('project_tasks', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that created this record.')
                ->after('created_at');
            $table->foreign('created_by')->references('id')->on('users');
            $table->unsignedBigInteger('updated_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that changed this record last.')
                ->after('updated_at');
            $table->foreign('updated_by')->references('id')->on('users');
            $table->unsignedBigInteger('deleted_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that deleted this record.')
                ->after('deleted_at');
            $table->foreign('deleted_by')->references('id')->on('users');
        });
        DB::statement(
            'UPDATE project_tasks SET
                created_by = (
                    SELECT causer_id
                    FROM activity_logs
                    WHERE subject_id = project_tasks.id
                      AND event = "created"
                      AND subject_type = "FluxErp\\\\Models\\\\ProjectTask"
                      AND causer_type = "FluxErp\\\\Models\\\\User"
                    ORDER BY id LIMIT 1
                )'
        );
        DB::statement(
            'UPDATE project_tasks SET
                updated_by = (
                    SELECT causer_id
                    FROM activity_logs
                    WHERE subject_id = project_tasks.id
                      AND event = "updated"
                      AND subject_type = "FluxErp\\\\Models\\\\ProjectTask"
                      AND causer_type = "FluxErp\\\\Models\\\\User"
                    ORDER BY id DESC LIMIT 1
                )'
        );
        DB::statement(
            'UPDATE project_tasks SET
                deleted_by = (
                    SELECT causer_id
                    FROM activity_logs
                    WHERE subject_id = project_tasks.id
                      AND event = "deleted"
                      AND subject_type = "FluxErp\\\\Models\\\\ProjectTask"
                      AND causer_type = "FluxErp\\\\Models\\\\User"
                    ORDER BY id DESC LIMIT 1
                )'
        );

        Schema::table('sepa_mandates', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that created this record.')
                ->after('created_at');
            $table->foreign('created_by')->references('id')->on('users');
            $table->unsignedBigInteger('updated_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that changed this record last.')
                ->after('updated_at');
            $table->foreign('updated_by')->references('id')->on('users');
            $table->unsignedBigInteger('deleted_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that deleted this record.')
                ->after('deleted_at');
            $table->foreign('deleted_by')->references('id')->on('users');
        });
        DB::statement(
            'UPDATE sepa_mandates SET
                created_by = (
                    SELECT causer_id
                    FROM activity_logs
                    WHERE subject_id = sepa_mandates.id
                      AND event = "created"
                      AND subject_type = "FluxErp\\\\Models\\\\SepaMandate"
                      AND causer_type = "FluxErp\\\\Models\\\\User"
                    ORDER BY id LIMIT 1
                )'
        );
        DB::statement(
            'UPDATE sepa_mandates SET
                updated_by = (
                    SELECT causer_id
                    FROM activity_logs
                    WHERE subject_id = sepa_mandates.id
                      AND event = "updated"
                      AND subject_type = "FluxErp\\\\Models\\\\SepaMandate"
                      AND causer_type = "FluxErp\\\\Models\\\\User"
                    ORDER BY id DESC LIMIT 1
                )'
        );
        DB::statement(
            'UPDATE sepa_mandates SET
                deleted_by = (
                    SELECT causer_id
                    FROM activity_logs
                    WHERE subject_id = sepa_mandates.id
                      AND event = "deleted"
                      AND subject_type = "FluxErp\\\\Models\\\\SepaMandate"
                      AND causer_type = "FluxErp\\\\Models\\\\User"
                    ORDER BY id DESC LIMIT 1
                )'
        );

        Schema::table('serial_numbers', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that created this record.')
                ->after('created_at');
            $table->foreign('created_by')->references('id')->on('users');
            $table->unsignedBigInteger('updated_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that changed this record last.')
                ->after('updated_at');
            $table->foreign('updated_by')->references('id')->on('users');
        });
        DB::statement(
            'UPDATE serial_numbers SET
                created_by = (
                    SELECT causer_id
                    FROM activity_logs
                    WHERE subject_id = serial_numbers.id
                      AND event = "created"
                      AND subject_type = "FluxErp\\\\Models\\\\SerialNumber"
                      AND causer_type = "FluxErp\\\\Models\\\\User"
                    ORDER BY id LIMIT 1
                )'
        );
        DB::statement(
            'UPDATE serial_numbers SET
                updated_by = (
                    SELECT causer_id
                    FROM activity_logs
                    WHERE subject_id = serial_numbers.id
                      AND event = "updated"
                      AND subject_type = "FluxErp\\\\Models\\\\SerialNumber"
                      AND causer_type = "FluxErp\\\\Models\\\\User"
                    ORDER BY id DESC LIMIT 1
                )'
        );

        Schema::table('serial_number_ranges', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that created this record.')
                ->after('created_at');
            $table->foreign('created_by')->references('id')->on('users');
            $table->unsignedBigInteger('updated_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that changed this record last.')
                ->after('updated_at');
            $table->foreign('updated_by')->references('id')->on('users');
            $table->unsignedBigInteger('deleted_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that deleted this record.')
                ->after('deleted_at');
            $table->foreign('deleted_by')->references('id')->on('users');
        });
        DB::statement(
            'UPDATE serial_number_ranges SET
                created_by = (
                    SELECT causer_id
                    FROM activity_logs
                    WHERE subject_id = serial_number_ranges.id
                      AND event = "created"
                      AND subject_type = "FluxErp\\\\Models\\\\SerialNumberRange"
                      AND causer_type = "FluxErp\\\\Models\\\\User"
                    ORDER BY id LIMIT 1
                )'
        );
        DB::statement(
            'UPDATE serial_number_ranges SET
                updated_by = (
                    SELECT causer_id
                    FROM activity_logs
                    WHERE subject_id = serial_number_ranges.id
                      AND event = "updated"
                      AND subject_type = "FluxErp\\\\Models\\\\SerialNumberRange"
                      AND causer_type = "FluxErp\\\\Models\\\\User"
                    ORDER BY id DESC LIMIT 1
                )'
        );
        DB::statement(
            'UPDATE serial_number_ranges SET
                deleted_by = (
                    SELECT causer_id
                    FROM activity_logs
                    WHERE subject_id = serial_number_ranges.id
                      AND event = "deleted"
                      AND subject_type = "FluxErp\\\\Models\\\\SerialNumberRange"
                      AND causer_type = "FluxErp\\\\Models\\\\User"
                    ORDER BY id DESC LIMIT 1
                )'
        );

        Schema::table('snapshots', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that created this record.')
                ->after('created_at');
            $table->foreign('created_by')->references('id')->on('users');
            $table->unsignedBigInteger('updated_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that changed this record last.')
                ->after('updated_at');
            $table->foreign('updated_by')->references('id')->on('users');
        });
        DB::statement(
            'UPDATE snapshots SET
                created_by = (
                    SELECT causer_id
                    FROM activity_logs
                    WHERE subject_id = snapshots.id
                      AND event = "created"
                      AND subject_type = "FluxErp\\\\Models\\\\Snapshot"
                      AND causer_type = "FluxErp\\\\Models\\\\User"
                    ORDER BY id LIMIT 1
                )'
        );
        DB::statement(
            'UPDATE snapshots SET
                updated_by = (
                    SELECT causer_id
                    FROM activity_logs
                    WHERE subject_id = snapshots.id
                      AND event = "updated"
                      AND subject_type = "FluxErp\\\\Models\\\\Snapshot"
                      AND causer_type = "FluxErp\\\\Models\\\\User"
                    ORDER BY id DESC LIMIT 1
                )'
        );

        Schema::table('stock_postings', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that created this record.')
                ->after('created_at');
            $table->foreign('created_by')->references('id')->on('users');
            $table->unsignedBigInteger('updated_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that changed this record last.')
                ->after('updated_at');
            $table->foreign('updated_by')->references('id')->on('users');
            $table->unsignedBigInteger('deleted_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that deleted this record.')
                ->after('deleted_at');
            $table->foreign('deleted_by')->references('id')->on('users');
        });
        DB::statement(
            'UPDATE stock_postings SET
                created_by = (
                    SELECT causer_id
                    FROM activity_logs
                    WHERE subject_id = stock_postings.id
                      AND event = "created"
                      AND subject_type = "FluxErp\\\\Models\\\\StockPosting"
                      AND causer_type = "FluxErp\\\\Models\\\\User"
                    ORDER BY id LIMIT 1
                )'
        );
        DB::statement(
            'UPDATE stock_postings SET
                updated_by = (
                    SELECT causer_id
                    FROM activity_logs
                    WHERE subject_id = stock_postings.id
                      AND event = "updated"
                      AND subject_type = "FluxErp\\\\Models\\\\StockPosting"
                      AND causer_type = "FluxErp\\\\Models\\\\User"
                    ORDER BY id DESC LIMIT 1
                )'
        );
        DB::statement(
            'UPDATE stock_postings SET
                deleted_by = (
                    SELECT causer_id
                    FROM activity_logs
                    WHERE subject_id = stock_postings.id
                      AND event = "deleted"
                      AND subject_type = "FluxErp\\\\Models\\\\StockPosting"
                      AND causer_type = "FluxErp\\\\Models\\\\User"
                    ORDER BY id DESC LIMIT 1
                )'
        );

        Schema::table('tickets', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that created this record.')
                ->after('created_at');
            $table->foreign('created_by')->references('id')->on('users');
            $table->unsignedBigInteger('updated_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that changed this record last.')
                ->after('updated_at');
            $table->foreign('updated_by')->references('id')->on('users');
            $table->unsignedBigInteger('deleted_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that deleted this record.')
                ->after('deleted_at');
            $table->foreign('deleted_by')->references('id')->on('users');
        });
        DB::statement(
            'UPDATE tickets SET
                created_by = (
                    SELECT causer_id
                    FROM activity_logs
                    WHERE subject_id = tickets.id
                      AND event = "created"
                      AND subject_type = "FluxErp\\\\Models\\\\Ticket"
                      AND causer_type = "FluxErp\\\\Models\\\\User"
                    ORDER BY id LIMIT 1
                )'
        );
        DB::statement(
            'UPDATE tickets SET
                updated_by = (
                    SELECT causer_id
                    FROM activity_logs
                    WHERE subject_id = tickets.id
                      AND event = "updated"
                      AND subject_type = "FluxErp\\\\Models\\\\Ticket"
                      AND causer_type = "FluxErp\\\\Models\\\\User"
                    ORDER BY id DESC LIMIT 1
                )'
        );
        DB::statement(
            'UPDATE tickets SET
                deleted_by = (
                    SELECT causer_id
                    FROM activity_logs
                    WHERE subject_id = tickets.id
                      AND event = "deleted"
                      AND subject_type = "FluxErp\\\\Models\\\\Ticket"
                      AND causer_type = "FluxErp\\\\Models\\\\User"
                    ORDER BY id DESC LIMIT 1
                )'
        );

        Schema::table('ticket_types', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that created this record.')
                ->after('created_at');
            $table->unsignedBigInteger('updated_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that changed this record last.')
                ->after('updated_at');
            $table->unsignedBigInteger('deleted_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that deleted this record.')
                ->after('deleted_at');
        });
        DB::statement(
            'UPDATE ticket_types SET
                created_by = (
                    SELECT causer_id
                    FROM activity_logs
                    WHERE subject_id = ticket_types.id
                      AND event = "created"
                      AND subject_type = "FluxErp\\\\Models\\\\TicketType"
                      AND causer_type = "FluxErp\\\\Models\\\\User"
                    ORDER BY id LIMIT 1
                )'
        );
        DB::statement(
            'UPDATE ticket_types SET
                updated_by = (
                    SELECT causer_id
                    FROM activity_logs
                    WHERE subject_id = ticket_types.id
                      AND event = "updated"
                      AND subject_type = "FluxErp\\\\Models\\\\TicketType"
                      AND causer_type = "FluxErp\\\\Models\\\\User"
                    ORDER BY id DESC LIMIT 1
                )'
        );
        DB::statement(
            'UPDATE ticket_types SET
                deleted_by = (
                    SELECT causer_id
                    FROM activity_logs
                    WHERE subject_id = ticket_types.id
                      AND event = "deleted"
                      AND subject_type = "FluxErp\\\\Models\\\\TicketType"
                      AND causer_type = "FluxErp\\\\Models\\\\User"
                    ORDER BY id DESC LIMIT 1
                )'
        );

        Schema::table('units', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that created this record.')
                ->after('created_at');
            $table->foreign('created_by')->references('id')->on('users');
            $table->unsignedBigInteger('updated_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that changed this record last.')
                ->after('updated_at');
            $table->foreign('updated_by')->references('id')->on('users');
            $table->unsignedBigInteger('deleted_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that deleted this record.')
                ->after('deleted_at');
            $table->foreign('deleted_by')->references('id')->on('users');
        });
        DB::statement(
            'UPDATE units SET
                created_by = (
                    SELECT causer_id
                    FROM activity_logs
                    WHERE subject_id = units.id
                      AND event = "created"
                      AND subject_type = "FluxErp\\\\Models\\\\Unit"
                      AND causer_type = "FluxErp\\\\Models\\\\User"
                    ORDER BY id LIMIT 1
                )'
        );
        DB::statement(
            'UPDATE units SET
                updated_by = (
                    SELECT causer_id
                    FROM activity_logs
                    WHERE subject_id = units.id
                      AND event = "updated"
                      AND subject_type = "FluxErp\\\\Models\\\\Unit"
                      AND causer_type = "FluxErp\\\\Models\\\\User"
                    ORDER BY id DESC LIMIT 1
                )'
        );
        DB::statement(
            'UPDATE units SET
                deleted_by = (
                    SELECT causer_id
                    FROM activity_logs
                    WHERE subject_id = units.id
                      AND event = "deleted"
                      AND subject_type = "FluxErp\\\\Models\\\\Unit"
                      AND causer_type = "FluxErp\\\\Models\\\\User"
                    ORDER BY id DESC LIMIT 1
                )'
        );

        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that created this record.')
                ->after('created_at');
            $table->foreign('created_by')->references('id')->on('users');
            $table->unsignedBigInteger('updated_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that changed this record last.')
                ->after('updated_at');
            $table->foreign('updated_by')->references('id')->on('users');
            $table->unsignedBigInteger('deleted_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that deleted this record.')
                ->after('deleted_at');
            $table->foreign('deleted_by')->references('id')->on('users');
        });
        DB::statement(
            'UPDATE users SET
                created_by = (
                    SELECT causer_id
                    FROM activity_logs
                    WHERE subject_id = users.id
                      AND event = "created"
                      AND subject_type = "FluxErp\\\\Models\\\\User"
                      AND causer_type = "FluxErp\\\\Models\\\\User"
                    ORDER BY id LIMIT 1
                )'
        );
        DB::statement(
            'UPDATE users SET
                updated_by = (
                    SELECT causer_id
                    FROM activity_logs
                    WHERE subject_id = users.id
                      AND event = "updated"
                      AND subject_type = "FluxErp\\\\Models\\\\User"
                      AND causer_type = "FluxErp\\\\Models\\\\User"
                    ORDER BY id DESC LIMIT 1
                )'
        );
        DB::statement(
            'UPDATE users SET
                deleted_by = (
                    SELECT causer_id
                    FROM activity_logs
                    WHERE subject_id = users.id
                      AND event = "deleted"
                      AND subject_type = "FluxErp\\\\Models\\\\User"
                      AND causer_type = "FluxErp\\\\Models\\\\User"
                    ORDER BY id DESC LIMIT 1
                )'
        );

        Schema::table('vat_rates', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that created this record.')
                ->after('created_at');
            $table->foreign('created_by')->references('id')->on('users');
            $table->unsignedBigInteger('updated_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that changed this record last.')
                ->after('updated_at');
            $table->foreign('updated_by')->references('id')->on('users');
            $table->unsignedBigInteger('deleted_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that deleted this record.')
                ->after('deleted_at');
            $table->foreign('deleted_by')->references('id')->on('users');
        });
        DB::statement(
            'UPDATE vat_rates SET
                created_by = (
                    SELECT causer_id
                    FROM activity_logs
                    WHERE subject_id = vat_rates.id
                      AND event = "created"
                      AND subject_type = "FluxErp\\\\Models\\\\VatRate"
                      AND causer_type = "FluxErp\\\\Models\\\\User"
                    ORDER BY id LIMIT 1
                )'
        );
        DB::statement(
            'UPDATE vat_rates SET
                updated_by = (
                    SELECT causer_id
                    FROM activity_logs
                    WHERE subject_id = vat_rates.id
                      AND event = "updated"
                      AND subject_type = "FluxErp\\\\Models\\\\VatRate"
                      AND causer_type = "FluxErp\\\\Models\\\\User"
                    ORDER BY id DESC LIMIT 1
                )'
        );
        DB::statement(
            'UPDATE vat_rates SET
                deleted_by = (
                    SELECT causer_id
                    FROM activity_logs
                    WHERE subject_id = vat_rates.id
                      AND event = "deleted"
                      AND subject_type = "FluxErp\\\\Models\\\\VatRate"
                      AND causer_type = "FluxErp\\\\Models\\\\User"
                    ORDER BY id DESC LIMIT 1
                )'
        );

        Schema::table('warehouses', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that created this record.')
                ->after('created_at');
            $table->foreign('created_by')->references('id')->on('users');
            $table->unsignedBigInteger('updated_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that changed this record last.')
                ->after('updated_at');
            $table->foreign('updated_by')->references('id')->on('users');
            $table->unsignedBigInteger('deleted_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that deleted this record.')
                ->after('deleted_at');
            $table->foreign('deleted_by')->references('id')->on('users');
        });
        DB::statement(
            'UPDATE warehouses SET
                created_by = (
                    SELECT causer_id
                    FROM activity_logs
                    WHERE subject_id = warehouses.id
                      AND event = "created"
                      AND subject_type = "FluxErp\\\\Models\\\\Warehouse"
                      AND causer_type = "FluxErp\\\\Models\\\\User"
                    ORDER BY id LIMIT 1
                )'
        );
        DB::statement(
            'UPDATE warehouses SET
                updated_by = (
                    SELECT causer_id
                    FROM activity_logs
                    WHERE subject_id = warehouses.id
                      AND event = "updated"
                      AND subject_type = "FluxErp\\\\Models\\\\Warehouse"
                      AND causer_type = "FluxErp\\\\Models\\\\User"
                    ORDER BY id DESC LIMIT 1
                )'
        );
        DB::statement(
            'UPDATE warehouses SET
                deleted_by = (
                    SELECT causer_id
                    FROM activity_logs
                    WHERE subject_id = warehouses.id
                      AND event = "deleted"
                      AND subject_type = "FluxErp\\\\Models\\\\Warehouse"
                      AND causer_type = "FluxErp\\\\Models\\\\User"
                    ORDER BY id DESC LIMIT 1
                )'
        );
    }
};
