<?php

return [
    'accepted' => 'The :attribute must be accepted.',
    'accepted_if' => 'The :attribute must be accepted when :other is :value.',
    'active_url' => 'The :attribute is not a valid URL.',
    'after' => 'The :attribute must be a date after :date.',
    'after_or_equal' => 'The :attribute must be a date after or equal to :date.',
    'alpha' => 'The :attribute must only contain letters.',
    'alpha_dash' => 'The :attribute must only contain letters, numbers, dashes and underscores.',
    'alpha_num' => 'The :attribute must only contain letters and numbers.',
    'array' => 'The :attribute must be an array.',
    'ascii' => 'The :attribute field must only contain single-byte alphanumeric characters and symbols.',
    'before' => 'The :attribute must be a date before :date.',
    'before_or_equal' => 'The :attribute must be a date before or equal to :date.',
    'between' => [
        'array' => 'The :attribute must have between :min and :max items.',
        'file' => 'The :attribute must be between :min and :max kilobytes.',
        'numeric' => 'The :attribute must be between :min and :max.',
        'string' => 'The :attribute must be between :min and :max characters.',
    ],
    'boolean' => 'The :attribute field must be true or false.',
    'can' => 'The :attribute field contains an unauthorized value.',
    'confirmed' => 'The :attribute confirmation does not match.',
    'contains' => 'The :attribute field is missing a required value.',
    'current_password' => 'The password is incorrect.',
    'date' => 'The :attribute is not a valid date.',
    'date_equals' => 'The :attribute must be a date equal to :date.',
    'date_format' => 'The :attribute does not match the format :format.',
    'decimal' => 'The :attribute field must have :decimal decimal places.',
    'declined' => 'The :attribute must be declined.',
    'declined_if' => 'The :attribute must be declined when :other is :value.',
    'different' => 'The :attribute and :other must be different.',
    'digits' => 'The :attribute must be :digits digits.',
    'digits_between' => 'The :attribute must be between :min and :max digits.',
    'dimensions' => 'The :attribute has invalid image dimensions.',
    'distinct' => 'The :attribute field has a duplicate value.',
    'doesnt_end_with' => 'The :attribute field must not end with one of the following: :values.',
    'doesnt_start_with' => 'The :attribute field must not start with one of the following: :values.',
    'email' => 'The :attribute must be a valid email address.',
    'ends_with' => 'The :attribute must end with one of the following: :values.',
    'enum' => 'The :attribute field value is not in the list of allowed values.',
    'exists' => 'The :attribute field value does not exist.',
    'extensions' => 'The :attribute field must have one of the following extensions: :values.',
    'file' => 'The :attribute must be a file.',
    'filled' => 'The :attribute field must have a value.',
    'gt' => [
        'array' => 'The :attribute must have more than :value items.',
        'file' => 'The :attribute must be greater than :value kilobytes.',
        'numeric' => 'The :attribute must be greater than :value.',
        'string' => 'The :attribute must be greater than :value characters.',
    ],
    'gte' => [
        'array' => 'The :attribute must have :value items or more.',
        'file' => 'The :attribute must be greater than or equal to :value kilobytes.',
        'numeric' => 'The :attribute must be greater than or equal to :value.',
        'string' => 'The :attribute must be greater than or equal to :value characters.',
    ],
    'hex_color' => 'The :attribute field must be a valid hexadecimal color.',
    'image' => 'The :attribute must be an image.',
    'in' => 'The :attribute field value is not in the list of allowed values.',
    'in_array' => 'The :attribute field does not exist in :other.',
    'integer' => 'The :attribute must be an integer.',
    'ip' => 'The :attribute must be a valid IP address.',
    'ipv4' => 'The :attribute must be a valid IPv4 address.',
    'ipv6' => 'The :attribute must be a valid IPv6 address.',
    'json' => 'The :attribute must be a valid JSON string.',
    'list' => 'The :attribute field must be a list.',
    'lowercase' => 'The :attribute field must be lowercase.',
    'lt' => [
        'array' => 'The :attribute must have less than :value items.',
        'file' => 'The :attribute must be less than :value kilobytes.',
        'numeric' => 'The :attribute must be less than :value.',
        'string' => 'The :attribute must be less than :value characters.',
    ],
    'lte' => [
        'array' => 'The :attribute must not have more than :value items.',
        'file' => 'The :attribute must be less than or equal to :value kilobytes.',
        'numeric' => 'The :attribute must be less than or equal to :value.',
        'string' => 'The :attribute must be less than or equal to :value characters.',
    ],
    'mac_address' => 'The :attribute must be a valid MAC address.',
    'max' => [
        'array' => 'The :attribute must not have more than :max items.',
        'file' => 'The :attribute must not be greater than :max kilobytes.',
        'numeric' => 'The :attribute must not be greater than :max.',
        'string' => 'The :attribute must not be greater than :max characters.',
    ],
    'max_digits' => 'The :attribute field must not have more than :max digits.',
    'mimes' => 'The :attribute must be a file of type: :values.',
    'mimetypes' => 'The :attribute must be a file of type: :values.',
    'min' => [
        'array' => 'The :attribute must have at least :min items.',
        'file' => 'The :attribute must be at least :min kilobytes.',
        'numeric' => 'The :attribute must be at least :min.',
        'string' => 'The :attribute must be at least :min characters.',
    ],
    'min_digits' => 'The :attribute field must have at least :min digits.',
    'missing' => 'The :attribute field must be missing.',
    'missing_if' => 'The :attribute field must be missing when :other is :value.',
    'missing_unless' => 'The :attribute field must be missing unless :other is :value.',
    'missing_with' => 'The :attribute field must be missing when :values is present.',
    'missing_with_all' => 'The :attribute field must be missing when :values are present.',
    'multiple_of' => 'The :attribute must be a multiple of :value.',
    'no_decimals' => 'The :attribute field must not have any decimal places.',
    'not_in' => 'The :attribute field must not be in the list.',
    'not_regex' => 'The :attribute format is invalid.',
    'numeric' => 'The :attribute must be a number.',
    'password' => [
        'letters' => 'The :attribute field must contain at least one letter.',
        'mixed' => 'The :attribute field must contain at least one uppercase and one lowercase letter.',
        'numbers' => 'The :attribute field must contain at least one number.',
        'symbols' => 'The :attribute field must contain at least one symbol.',
        'uncompromised' => 'The given :attribute has appeared in a data leak. Please choose a different :attribute.',
    ],
    'present' => 'The :attribute field must be present.',
    'present_if' => 'The :attribute field must be present when :other is :value.',
    'present_unless' => 'The :attribute field must be present unless :other is :value.',
    'present_with' => 'The :attribute field must be present when :values is present.',
    'present_with_all' => 'The :attribute field must be present when :values are present.',
    'prohibited' => 'The :attribute field is prohibited.',
    'prohibited_if' => 'The :attribute field is prohibited when :other is :value.',
    'prohibited_unless' => 'The :attribute field is prohibited unless :other is in :values.',
    'prohibits' => 'The :attribute field prohibits :other from being present.',
    'regex' => 'The :attribute format is invalid.',
    'required' => 'The :attribute field is required.',
    'required_array_keys' => 'The :attribute field must contain entries for: :values.',
    'required_if' => 'The :attribute field is required when :other is :value.',
    'required_if_accepted' => 'The :attribute field is required when :other is accepted.',
    'required_if_declined' => 'The :attribute field is required when :other is declined.',
    'required_unless' => 'The :attribute field is required unless :other is in :values.',
    'required_with' => 'The :attribute field is required when :values is present.',
    'required_with_all' => 'The :attribute field is required when :values are present.',
    'required_without' => 'The :attribute field is required when :values is not present.',
    'required_without_all' => 'The :attribute field is required when none of :values are present.',
    'same' => 'The :attribute and :other must match.',
    'size' => [
        'array' => 'The :attribute must contain :size items.',
        'file' => 'The :attribute must be :size kilobytes.',
        'numeric' => 'The :attribute must be :size.',
        'string' => 'The :attribute must be :size characters.',
    ],
    'starts_with' => 'The :attribute must start with one of the following: :values.',
    'string' => 'The :attribute must be a string.',
    'timezone' => 'The :attribute must be a valid timezone.',
    'ulid' => 'The :attribute field must be a valid ULID.',
    'unique' => 'The :attribute has already been taken.',
    'uploaded' => 'The :attribute failed to upload.',
    'uppercase' => 'The :attribute field must be uppercase.',
    'url' => 'The :attribute must be a valid URL.',
    'uuid' => 'The :attribute must be a valid UUID.',

    'attributes' => [
        'abbreviation' => 'Abbreviation',
        'account_holder' => 'Account Holder',
        'addition' => 'Addition',
        'address_delivery' => 'Delivery Address',
        'address_delivery.addition' => 'Addition',
        'address_delivery.city' => 'City',
        'address_delivery.company' => 'Company',
        'address_delivery.email_primary' => 'Primary Email',
        'address_delivery.firstname' => 'First Name',
        'address_delivery.id' => 'Delivery Address ID',
        'address_delivery.lastname' => 'Last Name',
        'address_delivery.latitude' => 'Latitude',
        'address_delivery.longitude' => 'Longitude',
        'address_delivery.mailbox' => 'Mailbox',
        'address_delivery.mailbox_city' => 'Mailbox City',
        'address_delivery.mailbox_zip' => 'Mailbox ZIP',
        'address_delivery.phone' => 'Phone',
        'address_delivery.salutation' => 'Salutation',
        'address_delivery.street' => 'Street',
        'address_delivery.title' => 'Title',
        'address_delivery.url' => 'URL',
        'address_delivery.zip' => 'ZIP',
        'address_delivery_id' => 'Delivery Address ID',
        'address_id' => 'Address ID',
        'address_invoice_id' => 'Invoice Address ID',
        'address_type_code' => 'Address Type Code',
        'address_types' => 'Address Types',
        'address_types.*' => 'Address Type',
        'addresses' => 'Addresses',
        'addresses.*.address_id' => 'Address ID',
        'addresses.*.address_type_id' => 'Address Type ID',
        'agent_id' => 'Agent ID',
        'amount' => 'Amount',
        'amount_bundle' => 'Bundle Amount',
        'amount_packed_products' => 'Amount of Packed Products',
        'approval_user_id' => 'Approval User ID',
        'assign' => 'Assign',
        'attachments' => 'Attachments',
        'attachments.*.categories.*' => 'Category',
        'attachments.*.custom_properties' => 'Custom Properties',
        'attachments.*.disk' => 'Disk',
        'attachments.*.file_name' => 'File Name',
        'attachments.*.media' => 'Media',
        'attachments.*.mime_type' => 'MIME Type',
        'attachments.*.name' => 'Name',
        'authenticatable_id' => 'Authenticatable ID',
        'authenticatable_type' => 'Authenticatable Type',
        'bank_connection_id' => 'Bank Connection ID',
        'bank_connections' => 'Bank Connections',
        'bank_connections.*' => 'Bank Connection',
        'bank_name' => 'Bank Name',
        'basic_unit' => 'Basic Unit',
        'bcc' => 'BCC',
        'bic' => 'BIC',
        'booking_date' => 'Booking Date',
        'budget' => 'Budget',
        'bundle_product_id' => 'Bundle Product ID',
        'bundle_products' => 'Bundle Products',
        'bundle_products.*.count' => 'Bundle Product Count',
        'bundle_products.*.id' => 'Bundle Product ID',
        'calendar_id' => 'Calendar ID',
        'can_login' => 'Can Login',
        'cart_id' => 'Cart ID',
        'categories' => 'Categories',
        'categories.*' => 'Category',
        'category_id' => 'Category ID',
        'cc' => 'CC',
        'ceo' => 'CEO',
        'channel' => 'Channel',
        'channel_value' => 'Channel Value',
        'city' => 'City',
        'client_code' => 'Client Code',
        'client_id' => 'Client ID',
        'clients' => 'Clients',
        'clients.*' => 'Client',
        'collection' => 'Collection',
        'collection_name' => 'Collection Name',
        'color' => 'Color',
        'columns' => 'Columns',
        'comment' => 'Comment',
        'commission' => 'Commission',
        'commission_rate' => 'Commission Rate',
        'commission_rate_id' => 'Commission Rate ID',
        'communicatable_id' => 'Communicatable ID',
        'communicatable_type' => 'Communicatable Type',
        'communication_type_enum' => 'Communication Type',
        'company' => 'Company',
        'confirm_option' => 'Confirm Option',
        'contact_bank_connection_id' => 'Contact Bank Connection ID',
        'contact_id' => 'Contact ID',
        'contact_options' => 'Contact Options',
        'contact_options.*.id' => 'Contact Option ID',
        'contact_options.*.is_primary' => 'Contact Option Primary',
        'contact_options.*.label' => 'Contact Option Label',
        'contact_options.*.type' => 'Contact Option Type',
        'contact_options.*.value' => 'Contact Option Value',
        'count' => 'Count',
        'counterpart_account_number' => 'Counterpart Account Number',
        'counterpart_bank_name' => 'Counterpart Bank Name',
        'counterpart_bic' => 'Counterpart BIC',
        'counterpart_iban' => 'Counterpart IBAN',
        'counterpart_name' => 'Counterpart Name',
        'country_id' => 'Country ID',
        'cover_media_id' => 'Cover Media ID',
        'credit_limit' => 'Credit Limit',
        'credit_line' => 'Credit Line',
        'creditor_identifier' => 'Creditor Identifier',
        'creditor_number' => 'Creditor Number',
        'cron' => 'Cron',
        'cron.methods' => 'Methods',
        'cron.methods.basic' => 'Basic',
        'cron.methods.dayConstraint' => 'Day Constraint',
        'cron.methods.timeConstraint' => 'Time Constraint',
        'cron.parameters' => 'Parameters',
        'cron.parameters.basic' => 'Basic',
        'cron.parameters.dayConstraint' => 'Day Constraint',
        'cron.parameters.timeConstraint' => 'Time Constraint',
        'currency_id' => 'Currency ID',
        'current_number' => 'Current Number',
        'custom_properties' => 'Custom Properties',
        'customer_delivery_date' => 'Customer Delivery Date',
        'customer_number' => 'Customer Number',
        'datanorm_long_text' => 'Datanorm Long Text',
        'date' => 'Date',
        'date_of_approval' => 'Date of Approval',
        'date_of_birth' => 'Date of Birth',
        'debtor_number' => 'Debtor Number',
        'delivery_state' => 'Delivery State',
        'department' => 'Department',
        'description' => 'Description',
        'dimension_height_mm' => 'Height in mm',
        'dimension_length_mm' => 'Length in mm',
        'dimension_width_mm' => 'Width in mm',
        'discount' => 'Discount',
        'discount.discount' => 'Discount',
        'discount.is_percentage' => 'Is Percentage',
        'discount_days' => 'Discount Days',
        'discount_groups' => 'Discount Groups',
        'discount_groups.*' => 'Discount Group',
        'discount_percent' => 'Discount Percent',
        'discount_percentage' => 'Discount Percentage',
        'discounts' => 'Discounts',
        'discounts.*' => 'Discount',
        'discounts.*.discount' => 'Discount',
        'discounts.*.is_percentage' => 'Is Percentage',
        'discounts.*.sort_number' => 'Sort Number',
        'disk' => 'Disk',
        'due_at' => 'Due At',
        'due_date' => 'Due Date',
        'ean' => 'EAN',
        'ean_code' => 'EAN Code',
        'email' => 'Email',
        'email_primary' => 'Primary Email',
        'encryption' => 'Encryption',
        'end' => 'End',
        'end_date' => 'End Date',
        'ended_at' => 'Ended At',
        'endpoint' => 'Endpoint',
        'ends_at' => 'Ends At',
        'event' => 'Event',
        'excluded' => 'Excluded',
        'excluded.*' => 'Excluded',
        'expense_ledger_account_id' => 'Expense Ledger Account ID',
        'extended_props' => 'Extended Properties',
        'fax' => 'Fax',
        'field_id' => 'Field ID',
        'field_type' => 'Field Type',
        'file_name' => 'File Name',
        'finish' => 'Finish',
        'firstname' => 'First Name',
        'footer' => 'Footer',
        'footer_text' => 'Footer Text',
        'form_id' => 'Form ID',
        'from' => 'From',
        'give' => 'Give',
        'group' => 'Group',
        'guard_name' => 'Guard Name',
        'has_delivery_lock' => 'Has Delivery Lock',
        'has_logistic_notify_number' => 'Has Logistic Notify Number',
        'has_logistic_notify_phone_number' => 'Has Logistic Notify Phone Number',
        'has_repeatable_events' => 'Has Repeatable Events',
        'has_sensitive_reminder' => 'Has Sensitive Reminder',
        'has_valid_certificate' => 'Has Valid Certificate',
        'header' => 'Header',
        'header_discount' => 'Header Discount',
        'host' => 'Host',
        'html' => 'HTML',
        'html_body' => 'HTML Body',
        'iban' => 'IBAN',
        'id' => 'ID',
        'instructed_execution_date' => 'Instructed Execution Date',
        'invited_addresses' => 'Invited Addresses',
        'invited_addresses.*.id' => 'Invited Address ID',
        'invited_addresses.*.status' => 'Invited Address Status',
        'invited_users' => 'Invited Users',
        'invited_users.*.id' => 'Invited User ID',
        'invited_users.*.status' => 'Invited User Status',
        'invoice_date' => 'Invoice Date',
        'invoice_number' => 'Invoice Number',
        'is_active' => 'Is Active',
        'is_active_export_to_web_shop' => 'Is Active Export to Web Shop',
        'is_all_day' => 'Is All Day',
        'is_alternative' => 'Is Alternative',
        'is_anonymous' => 'Is Anonymous',
        'is_auto_assign' => 'Is Auto Assign',
        'is_auto_create_serial_number' => 'Is Auto Create Serial Number',
        'is_automatic' => 'Is Automatic',
        'is_billable' => 'Is Billable',
        'is_broadcast' => 'Is Broadcast',
        'is_bundle' => 'Is Bundle',
        'is_bundle_position' => 'Is Bundle Position',
        'is_confirmed' => 'Is Confirmed',
        'is_customer_editable' => 'Is Customer Editable',
        'is_daily_work_time' => 'Is Daily Work Time',
        'is_default' => 'Is Default',
        'is_delivery_address' => 'Is Delivery Address',
        'is_direct_debit' => 'Is Direct Debit',
        'is_eu_country' => 'Is EU Country',
        'is_free_text' => 'Is Free Text',
        'is_frontend_visible' => 'Is Frontend Visible',
        'is_hidden' => 'Is Hidden',
        'is_highlight' => 'Is Highlight',
        'is_imported' => 'Is Imported',
        'is_instant_payment' => 'Is Instant Payment',
        'is_internal' => 'Is Internal',
        'is_invoice_address' => 'Is Invoice Address',
        'is_locked' => 'Is Locked',
        'is_main_address' => 'Is Main Address',
        'is_merge_invoice' => 'Is Merge Invoice',
        'is_net' => 'Is Net',
        'is_new_customer' => 'Is New Customer',
        'is_nos' => 'Is NOS',
        'is_notifiable' => 'Is Notifiable',
        'is_o_auth' => 'Is OAuth',
        'is_paid' => 'Is Paid',
        'is_pause' => 'Is Pause',
        'is_percentage' => 'Is Percentage',
        'is_portal_public' => 'Is Portal Public',
        'is_pre_filled' => 'Is Pre-filled',
        'is_product_serial_number' => 'Is Product Serial Number',
        'is_public' => 'Is Public',
        'is_purchase' => 'Is Purchase',
        'is_required_manufacturer_serial_number' => 'Is Required Manufacturer Serial Number',
        'is_required_product_serial_number' => 'Is Required Product Serial Number',
        'is_sales' => 'Is Sales',
        'is_seen' => 'Is Seen',
        'is_service' => 'Is Service',
        'is_shipping_free' => 'Is Shipping Free',
        'is_sticky' => 'Is Sticky',
        'is_translatable' => 'Is Translatable',
        'is_unique' => 'Is Unique',
        'is_watchlist' => 'Is Watchlist',
        'iso' => 'ISO',
        'iso_alpha2' => 'ISO Alpha2',
        'iso_alpha3' => 'ISO Alpha3',
        'iso_name' => 'ISO Name',
        'iso_numeric' => 'ISO Numeric',
        'key' => 'Key',
        'keys' => 'Keys',
        'keys.auth' => 'Auth',
        'keys.p256dh' => 'P256dh',
        'label' => 'Label',
        'language_code' => 'Language Code',
        'language_id' => 'Language ID',
        'lastname' => 'Last Name',
        'latitude' => 'Latitude',
        'lay_out_user_id' => 'Layout User ID',
        'ledger_account_id' => 'Ledger Account ID',
        'ledger_account_type_enum' => 'Ledger Account Type',
        'length' => 'Length',
        'logistic_note' => 'Logistic Note',
        'longitude' => 'Longitude',
        'mail_account_id' => 'Mail Account ID',
        'mail_accounts' => 'Mail Accounts',
        'mail_accounts.*' => 'Mail Account',
        'mail_body' => 'Mail Body',
        'mail_cc' => 'CC',
        'mail_cc.*' => 'CC',
        'mail_folder_id' => 'Mail Folder ID',
        'mail_subject' => 'Mail Subject',
        'mail_to' => 'Mail To',
        'mail_to.*' => 'Mail To',
        'mailbox' => 'Mailbox',
        'mailbox_city' => 'Mailbox City',
        'mailbox_zip' => 'Mailbox ZIP',
        'main_address' => 'Main Address',
        'main_address.addition' => 'Addition',
        'main_address.address_types' => 'Address Types',
        'main_address.address_types.*' => 'Address Type',
        'main_address.can_login' => 'Can Login',
        'main_address.city' => 'City',
        'main_address.company' => 'Company',
        'main_address.contact_options' => 'Contact Options',
        'main_address.contact_options.*.id' => 'Contact Option ID',
        'main_address.contact_options.*.is_primary' => 'Contact Option Primary',
        'main_address.contact_options.*.label' => 'Contact Option Label',
        'main_address.contact_options.*.type' => 'Contact Option Type',
        'main_address.contact_options.*.value' => 'Contact Option Value',
        'main_address.country_id' => 'Country ID',
        'main_address.date_of_birth' => 'Date of Birth',
        'main_address.department' => 'Department',
        'main_address.email' => 'Email',
        'main_address.email_primary' => 'Primary Email',
        'main_address.firstname' => 'First Name',
        'main_address.is_active' => 'Is Active',
        'main_address.is_delivery_address' => 'Is Delivery Address',
        'main_address.is_invoice_address' => 'Is Invoice Address',
        'main_address.is_main_address' => 'Is Main Address',
        'main_address.language_id' => 'Language ID',
        'main_address.lastname' => 'Last Name',
        'main_address.latitude' => 'Latitude',
        'main_address.longitude' => 'Longitude',
        'main_address.mailbox' => 'Mailbox',
        'main_address.mailbox_city' => 'Mailbox City',
        'main_address.mailbox_zip' => 'Mailbox ZIP',
        'main_address.password' => 'Password',
        'main_address.permissions' => 'Permissions',
        'main_address.permissions.*' => 'Permission',
        'main_address.phone' => 'Phone',
        'main_address.salutation' => 'Salutation',
        'main_address.street' => 'Street',
        'main_address.tags' => 'Tags',
        'main_address.tags.*' => 'Tag',
        'main_address.title' => 'Title',
        'main_address.url' => 'URL',
        'main_address.uuid' => 'UUID',
        'main_address.zip' => 'ZIP',
        'manufacturer_product_number' => 'Manufacturer Product Number',
        'margin' => 'Margin',
        'max_delivery_time' => 'Max Delivery Time',
        'max_purchase' => 'Max Purchase',
        'media' => 'Media',
        'media.id' => 'Media ID',
        'media_id' => 'Media ID',
        'media_type' => 'Media Type',
        'message_id' => 'Message ID',
        'message_uid' => 'Message UID',
        'migrate' => 'Migrate',
        'mime_type' => 'MIME Type',
        'min_delivery_time' => 'Min Delivery Time',
        'min_purchase' => 'Min Purchase',
        'model_id' => 'Model ID',
        'model_type' => 'Model Type',
        'name' => 'Name',
        'notes' => 'Notes',
        'notification_type' => 'Notification Type',
        'number' => 'Number',
        'number_of_packages' => 'Number of Packages',
        'opening_hours' => 'Opening Hours',
        'options' => 'Options',
        'options.*' => 'Option',
        'order_column' => 'Order Column',
        'order_date' => 'Order Date',
        'order_id' => 'Order ID',
        'order_number' => 'Order Number',
        'order_position_id' => 'Order Position ID',
        'order_positions' => 'Order Positions',
        'order_positions.*.amount' => 'Amount',
        'order_positions.*.id' => 'Order Position ID',
        'order_type_enum' => 'Order Type',
        'order_type_id' => 'Order Type ID',
        'ordering' => 'Ordering',
        'orders' => 'Orders',
        'orders.*.amount' => 'Amount',
        'orders.*.order_id' => 'Order ID',
        'origin_position_id' => 'Origin Position ID',
        'original_start' => 'Original Start',
        'packages' => 'Packages',
        'packages.*' => 'Package',
        'parameters' => 'Parameters',
        'parent_id' => 'Parent ID',
        'password' => 'Password',
        'paused_time_ms' => 'Paused Time in ms',
        'payment_discount_percent' => 'Payment Discount Percent',
        'payment_discount_percentage' => 'Payment Discount Percentage',
        'payment_discount_target' => 'Discount Target',
        'payment_reminder_current_level' => 'Current Payment Reminder Level',
        'payment_reminder_days_1' => 'Payment Reminder Day 1',
        'payment_reminder_days_2' => 'Payment Reminder Day 2',
        'payment_reminder_days_3' => 'Payment Reminder Day 3',
        'payment_reminder_email_text' => 'Payment Reminder Email Text',
        'payment_reminder_next_date' => 'Next Reminder Date',
        'payment_reminder_text' => 'Payment Reminder Text',
        'payment_run_type_enum' => 'Payment Run Type',
        'payment_state' => 'Payment State',
        'payment_target' => 'Payment Target',
        'payment_target_days' => 'Payment Target Days',
        'payment_texts' => 'Payment Texts',
        'payment_type_id' => 'Payment Type ID',
        'permissions' => 'Permissions',
        'permissions.*' => 'Permission',
        'phone' => 'Phone',
        'port' => 'Port',
        'possible_delivery_date' => 'Possible Delivery Date',
        'postcode' => 'Postcode',
        'posting' => 'Posting',
        'posting_account' => 'Posting Account',
        'prefix' => 'Prefix',
        'preview' => 'Preview',
        'price' => 'Price',
        'price_id' => 'Price ID',
        'price_list_code' => 'Price List Code',
        'price_list_id' => 'Price List ID',
        'prices' => 'Prices',
        'prices.*.price' => 'Price',
        'prices.*.price_list_id' => 'Price List ID',
        'print_layouts' => 'Print Layouts',
        'print_layouts.*' => 'Print Layout',
        'priority' => 'Priority',
        'product_cross_sellings.*.id' => 'Product Cross Sellings ID',
        'product_cross_sellings.*.is_active' => 'Is Active',
        'product_cross_sellings.*.name' => 'Name',
        'product_cross_sellings.*.order_column' => 'Order Column',
        'product_cross_sellings.*.products' => 'Products',
        'product_cross_sellings.*.products.*' => 'Product',
        'product_cross_sellings.*.uuid' => 'UUID',
        'product_id' => 'Product ID',
        'product_number' => 'Product Number',
        'product_option_group_id' => 'Product Option Group ID',
        'product_options' => 'Product Options',
        'product_options.*' => 'Product Option',
        'product_options.*.*' => 'Product Option',
        'product_options.*.id' => 'Product Option ID',
        'product_options.*.name' => 'Product Option Name',
        'product_properties' => 'Product Properties',
        'product_properties.*.id' => 'Product Property ID',
        'product_properties.*.value' => 'Product Property Value',
        'products' => 'Products',
        'products.*' => 'Product',
        'progress' => 'Progress',
        'project_id' => 'Project ID',
        'project_number' => 'Project Number',
        'protocol' => 'Protocol',
        'provision' => 'Provision',
        'purchase_invoice_id' => 'Purchase Invoice ID',
        'purchase_invoice_positions' => 'Purchase Invoice Positions',
        'purchase_invoice_positions.*.amount' => 'Amount',
        'purchase_invoice_positions.*.id' => 'ID',
        'purchase_invoice_positions.*.ledger_account_id' => 'Ledger Account ID',
        'purchase_invoice_positions.*.name' => 'Name',
        'purchase_invoice_positions.*.product_id' => 'Product ID',
        'purchase_invoice_positions.*.total_price' => 'Total Price',
        'purchase_invoice_positions.*.unit_price' => 'Unit Price',
        'purchase_invoice_positions.*.uuid' => 'UUID',
        'purchase_invoice_positions.*.vat_rate_id' => 'VAT Rate ID',
        'purchase_invoice_positions.*amount' => 'Amount',
        'purchase_invoice_positions.*id' => 'Purchase Invoice Position ID',
        'purchase_invoice_positions.*ledger_account_id' => 'Ledger Account ID',
        'purchase_invoice_positions.*name' => 'Name',
        'purchase_invoice_positions.*product_id' => 'Product ID',
        'purchase_invoice_positions.*total_price' => 'Total Price',
        'purchase_invoice_positions.*unit_price' => 'Unit Price',
        'purchase_invoice_positions.*vat_rate_id' => 'VAT Rate ID',
        'purchase_payment_type_id' => 'Purchase Payment Type ID',
        'purchase_price' => 'Purchase Price',
        'purchase_steps' => 'Purchase Steps',
        'purchase_unit_id' => 'Purchase Unit ID',
        'purpose' => 'Purpose',
        'rate_percentage' => 'Rate Percentage',
        'recurrences' => 'Recurrences',
        'reference_unit_id' => 'Reference Unit ID',
        'reminder_body' => 'Reminder Text',
        'reminder_level' => 'Reminder Level',
        'reminder_subject' => 'Reminder Subject',
        'repeat' => 'Repeat',
        'repeat.interval' => 'Interval',
        'repeat.monthly' => 'Monthly',
        'repeat.unit' => 'Unit',
        'repeat.weekdays' => 'Weekdays',
        'repeat.weekdays.*' => 'Weekday',
        'repeat_end' => 'Repeat End',
        'requires_approval' => 'Requires Approval',
        'requires_manual_transfer' => 'Requires Manual Transfer',
        'response' => 'Response',
        'response_id' => 'Response ID',
        'responsible_user_id' => 'Responsible User ID',
        'restock_time' => 'Restock Time',
        'roles' => 'Roles',
        'roles.*' => 'Role',
        'rollback' => 'Rollback',
        'rounding_method_enum' => 'Rounding Method',
        'rounding_mode' => 'Rounding Mode',
        'rounding_number' => 'Rounding Number',
        'rounding_precision' => 'Rounding Precision',
        'salutation' => 'Salutation',
        'section_id' => 'Section ID',
        'selling_unit' => 'Selling Unit',
        'seo_keywords' => 'SEO Keywords',
        'sepa_text' => 'SEPA Text',
        'serial_number' => 'Serial Number',
        'serial_number_range_id' => 'Serial Number Range ID',
        'session_id' => 'Session ID',
        'settings' => 'Settings',
        'shipping_costs_net_price' => 'Shipping Costs Net Price',
        'signed_date' => 'Signed Date',
        'simulate' => 'Simulate',
        'slug' => 'Slug',
        'smtp_email' => 'SMTP Email',
        'smtp_encryption' => 'SMTP Encryption',
        'smtp_host' => 'SMTP Host',
        'smtp_mailer' => 'SMTP Mailer',
        'smtp_password' => 'SMTP Password',
        'smtp_port' => 'SMTP Port',
        'sort_number' => 'Sort Number',
        'start' => 'Start',
        'start_date' => 'Start Date',
        'start_number' => 'Start Number',
        'started_at' => 'Started At',
        'state' => 'State',
        'stock' => 'Stock',
        'stores_serial_numbers' => 'Stores Serial Numbers',
        'street' => 'Street',
        'subject' => 'Subject',
        'suffix' => 'Suffix',
        'supplier_contact_id' => 'Supplier Contact ID',
        'suppliers' => 'Suppliers',
        'suppliers.*.contact_id' => 'Supplier Contact ID',
        'suppliers.*.manufacturer_product_number' => 'Manufacturer Product Number',
        'suppliers.*.purchase_price' => 'Purchase Price',
        'symbol' => 'Symbol',
        'sync' => 'Sync',
        'system_delivery_date' => 'System Delivery Date',
        'system_delivery_date_end' => 'System Delivery Date End',
        'tags' => 'Tags',
        'tags.*' => 'Tag',
        'terms_and_conditions' => 'Terms and Conditions',
        'text' => 'Text',
        'text_body' => 'Text Body',
        'ticket_id' => 'Ticket ID',
        'ticket_type_id' => 'Ticket Type ID',
        'till' => 'Till',
        'time_budget' => 'Time Budget',
        'time_unit_enum' => 'Time Unit',
        'title' => 'Title',
        'to' => 'To',
        'total_net_price' => 'Total Net Price',
        'total_price' => 'Total Price',
        'trackable_id' => 'Trackable ID',
        'trackable_type' => 'Trackable Type',
        'tracking_email' => 'Tracking Email',
        'type' => 'Type',
        'unit_gram_weight' => 'Unit Gram Weight',
        'unit_id' => 'Unit ID',
        'unit_price' => 'Unit Price',
        'unit_price_price_list_id' => 'Unit Price Price List ID',
        'url' => 'URL',
        'user_code' => 'User Code',
        'user_id' => 'User ID',
        'users' => 'Users',
        'users.*' => 'User',
        'uuid' => 'UUID',
        'validations' => 'Validations',
        'validations.*' => 'Validations',
        'value' => 'Value',
        'value_date' => 'Value Date',
        'values' => 'Values',
        'vat_id' => 'VAT ID',
        'vat_rate_id' => 'VAT Rate ID',
        'vendor_customer_number' => 'Vendor Customer Number',
        'view' => 'View',
        'warehouse_id' => 'Warehouse ID',
        'warning_stock_amount' => 'Warning Stock Amount',
        'website' => 'Website',
        'weight_gram' => 'Weight in Grams',
        'work_time_type_id' => 'Work Time Type ID',
        'zip' => 'ZIP',
    ],
];
