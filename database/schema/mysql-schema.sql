/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
DROP TABLE IF EXISTS `activity_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `activity_log` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `causer_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `causer_id` bigint unsigned DEFAULT NULL,
  `subject_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subject_id` bigint unsigned DEFAULT NULL,
  `event` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `log_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `properties` json DEFAULT NULL,
  `batch_uuid` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `causer` (`causer_type`,`causer_id`),
  KEY `subject` (`subject_type`,`subject_id`),
  KEY `activity_log_log_name_event_index` (`log_name`,`event`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `additional_column_sets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `additional_column_sets` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `additional_column_sets_name_unique` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `additional_columns`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `additional_columns` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned DEFAULT NULL,
  `field_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'text',
  `label` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `config` json DEFAULT NULL,
  `validations` json DEFAULT NULL,
  `values` json DEFAULT NULL,
  `is_translatable` tinyint(1) NOT NULL DEFAULT '0',
  `is_customer_editable` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'If set to true the customer can edit this field in the customer portal.',
  `is_frontend_visible` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `additional_columns_name_model_type_model_id_unique` (`name`,`model_type`,`model_id`),
  KEY `additional_columns_model_type_model_id_index` (`model_type`,`model_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `address_address_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `address_address_type` (
  `address_id` bigint unsigned NOT NULL COMMENT 'A unique identifier number for the table addresses.',
  `address_type_id` bigint unsigned NOT NULL COMMENT 'A unique identifier number for the table address types.',
  PRIMARY KEY (`address_id`,`address_type_id`),
  KEY `address_address_type_address_type_id_foreign` (`address_type_id`),
  CONSTRAINT `address_address_type_address_id_foreign` FOREIGN KEY (`address_id`) REFERENCES `addresses` (`id`),
  CONSTRAINT `address_address_type_address_type_id_foreign` FOREIGN KEY (`address_type_id`) REFERENCES `address_types` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `address_address_type_order`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `address_address_type_order` (
  `order_id` bigint unsigned NOT NULL COMMENT 'A unique identifier number for the table orders.',
  `address_id` bigint unsigned NOT NULL COMMENT 'A unique identifier number for the table addresses.',
  `address_type_id` bigint unsigned NOT NULL COMMENT 'A unique identifier number for the table address types.',
  `address` json DEFAULT NULL,
  PRIMARY KEY (`address_id`,`address_type_id`,`order_id`),
  KEY `address_address_type_order_order_id_foreign` (`order_id`),
  KEY `address_address_type_order_address_type_id_foreign` (`address_type_id`),
  CONSTRAINT `address_address_type_order_address_id_foreign` FOREIGN KEY (`address_id`) REFERENCES `addresses` (`id`),
  CONSTRAINT `address_address_type_order_address_type_id_foreign` FOREIGN KEY (`address_type_id`) REFERENCES `address_types` (`id`),
  CONSTRAINT `address_address_type_order_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `address_product`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `address_product` (
  `address_id` bigint unsigned NOT NULL,
  `product_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`address_id`,`product_id`),
  KEY `address_product_product_id_foreign` (`product_id`),
  CONSTRAINT `address_product_address_id_foreign` FOREIGN KEY (`address_id`) REFERENCES `addresses` (`id`),
  CONSTRAINT `address_product_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `address_sanitizer_geocode_results`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `address_sanitizer_geocode_results` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `payload_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `address` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `result` json NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `address_sanitizer_geocode_results_payload_hash_index` (`payload_hash`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `address_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `address_types` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT 'Id of the record.',
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Uuid of the record.',
  `client_id` bigint unsigned NOT NULL COMMENT 'A unique identifier number for the table clients.',
  `address_type_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Used for special queries or functions, eg. order always need an address with address type ''inv'' ( invoice ).',
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_locked` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Determines if record can be deleted. True: can not be deleted.',
  `is_unique` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Determines if only one of this type can exist in orders or addresses. True: needs to be unique.',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'A timestamp reflecting the time of record-creation.',
  `created_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL COMMENT 'A timestamp reflecting the time of the last change for this record.',
  `updated_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT 'A timestamp reflecting the time of record-deletion.',
  `deleted_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `address_types_client_id_address_type_code_unique` (`client_id`,`address_type_code`),
  CONSTRAINT `address_types_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `addresses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `addresses` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `client_id` bigint unsigned NOT NULL COMMENT 'A unique identifier number for the table clients.',
  `language_id` bigint unsigned DEFAULT NULL,
  `country_id` bigint unsigned DEFAULT NULL,
  `contact_id` bigint unsigned NOT NULL,
  `company` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `salutation` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `firstname` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `lastname` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `addition` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mailbox` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mailbox_city` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mailbox_zip` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `latitude` decimal(15,12) DEFAULT NULL,
  `longitude` decimal(15,12) DEFAULT NULL,
  `zip` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `street` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_primary` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `department` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `has_formal_salutation` tinyint(1) NOT NULL DEFAULT '1',
  `is_main_address` tinyint(1) NOT NULL DEFAULT '0',
  `is_invoice_address` tinyint(1) NOT NULL DEFAULT '0',
  `is_dark_mode` tinyint(1) NOT NULL DEFAULT '0',
  `is_delivery_address` tinyint(1) NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `can_login` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'A timestamp reflecting the time of record-creation.',
  `created_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL COMMENT 'A timestamp reflecting the time of the last change for this record.',
  `updated_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT 'A timestamp reflecting the time of record-deletion.',
  `deleted_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `addresses_contact_id_foreign` (`contact_id`),
  KEY `addresses_country_id_foreign` (`country_id`),
  KEY `addresses_language_id_foreign` (`language_id`),
  KEY `addresses_client_id_foreign` (`client_id`),
  KEY `addresses_login_name_index` (`email`),
  CONSTRAINT `addresses_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`),
  CONSTRAINT `addresses_contact_id_foreign` FOREIGN KEY (`contact_id`) REFERENCES `contacts` (`id`),
  CONSTRAINT `addresses_country_id_foreign` FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`),
  CONSTRAINT `addresses_language_id_foreign` FOREIGN KEY (`language_id`) REFERENCES `languages` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `bank_connection_client`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `bank_connection_client` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `client_id` bigint unsigned NOT NULL,
  `bank_connection_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `bank_connection_client_client_id_foreign` (`client_id`),
  KEY `bank_connection_client_bank_connection_id_foreign` (`bank_connection_id`),
  CONSTRAINT `bank_connection_client_bank_connection_id_foreign` FOREIGN KEY (`bank_connection_id`) REFERENCES `bank_connections` (`id`) ON DELETE CASCADE,
  CONSTRAINT `bank_connection_client_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `bank_connections`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `bank_connections` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `currency_id` bigint unsigned DEFAULT NULL,
  `ledger_account_id` bigint unsigned DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `account_holder` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bank_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `iban` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bic` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `credit_limit` int DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `deleted_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `bank_connections_iban_unique` (`iban`),
  KEY `accounts_currency_id_foreign` (`currency_id`),
  KEY `bank_connections_ledger_account_id_foreign` (`ledger_account_id`),
  CONSTRAINT `accounts_currency_id_foreign` FOREIGN KEY (`currency_id`) REFERENCES `currencies` (`id`),
  CONSTRAINT `bank_connections_ledger_account_id_foreign` FOREIGN KEY (`ledger_account_id`) REFERENCES `ledger_accounts` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `calendar_events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `calendar_events` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `ulid` char(26) COLLATE utf8mb4_unicode_ci NOT NULL,
  `calendar_id` bigint unsigned NOT NULL,
  `start` datetime NOT NULL,
  `end` datetime DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `repeat` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `repeat_end` datetime DEFAULT NULL,
  `recurrences` int unsigned DEFAULT NULL,
  `excluded` json DEFAULT NULL,
  `is_all_day` tinyint(1) NOT NULL DEFAULT '0',
  `extended_props` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `calendar_events_calendar_id_foreign` (`calendar_id`),
  CONSTRAINT `calendar_events_calendar_id_foreign` FOREIGN KEY (`calendar_id`) REFERENCES `calendars` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `calendar_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `calendar_groups` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` bigint unsigned DEFAULT NULL,
  `calendarable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `calendarable_id` bigint unsigned NOT NULL,
  `calendar_id` bigint unsigned DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `calendar_groups_calendarable_type_calendarable_id_index` (`calendarable_type`,`calendarable_id`),
  KEY `calendar_groups_parent_id_foreign` (`parent_id`),
  KEY `calendar_groups_calendar_id_foreign` (`calendar_id`),
  CONSTRAINT `calendar_groups_calendar_id_foreign` FOREIGN KEY (`calendar_id`) REFERENCES `calendars` (`id`) ON DELETE CASCADE,
  CONSTRAINT `calendar_groups_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `calendar_groups` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `calendarables`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `calendarables` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `calendar_id` bigint unsigned NOT NULL,
  `calendarable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `calendarable_id` bigint unsigned NOT NULL,
  `permission` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'owner',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `calendarable_unique` (`calendar_id`,`calendarable_id`,`calendarable_type`),
  KEY `calendarables_calendarable_type_calendarable_id_index` (`calendarable_type`,`calendarable_id`),
  CONSTRAINT `calendarables_calendar_id_foreign` FOREIGN KEY (`calendar_id`) REFERENCES `calendars` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `calendars`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `calendars` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `color` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '#3f51b5',
  `has_notifications` tinyint(1) NOT NULL DEFAULT '1',
  `has_repeatable_events` tinyint(1) NOT NULL DEFAULT '1',
  `is_editable` tinyint(1) NOT NULL DEFAULT '1',
  `is_public` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cart_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cart_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cart_id` bigint unsigned NOT NULL,
  `product_id` bigint unsigned DEFAULT NULL,
  `vat_rate_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `amount` decimal(40,10) NOT NULL,
  `price` decimal(40,10) NOT NULL,
  `total_net` decimal(40,10) NOT NULL,
  `total_gross` decimal(40,10) NOT NULL,
  `total` decimal(40,10) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cart_items_cart_id_foreign` (`cart_id`),
  KEY `cart_items_product_id_foreign` (`product_id`),
  KEY `cart_items_vat_rate_id_foreign` (`vat_rate_id`),
  CONSTRAINT `cart_items_cart_id_foreign` FOREIGN KEY (`cart_id`) REFERENCES `carts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cart_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cart_items_vat_rate_id_foreign` FOREIGN KEY (`vat_rate_id`) REFERENCES `vat_rates` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `carts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `carts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `authenticatable_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `authenticatable_id` bigint unsigned DEFAULT NULL,
  `payment_type_id` bigint unsigned DEFAULT NULL,
  `price_list_id` bigint unsigned NOT NULL,
  `session_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `total` decimal(40,10) DEFAULT NULL,
  `is_portal_public` tinyint(1) NOT NULL DEFAULT '0',
  `is_public` tinyint(1) NOT NULL DEFAULT '0',
  `is_watchlist` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `deleted_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `carts_authenticatable_type_authenticatable_id_index` (`authenticatable_type`,`authenticatable_id`),
  KEY `carts_payment_type_id_foreign` (`payment_type_id`),
  KEY `carts_price_list_id_foreign` (`price_list_id`),
  KEY `carts_session_id_index` (`session_id`),
  CONSTRAINT `carts_payment_type_id_foreign` FOREIGN KEY (`payment_type_id`) REFERENCES `payment_types` (`id`) ON DELETE SET NULL,
  CONSTRAINT `carts_price_list_id_foreign` FOREIGN KEY (`price_list_id`) REFERENCES `price_lists` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `parent_id` bigint unsigned DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sort_number` int unsigned NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `categories_parent_id_foreign` (`parent_id`),
  CONSTRAINT `categories_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `categorizables`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `categorizables` (
  `category_id` bigint unsigned NOT NULL,
  `categorizable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `categorizable_id` bigint unsigned NOT NULL,
  UNIQUE KEY `categorizables_ids_type_unique` (`category_id`,`categorizable_id`,`categorizable_type`),
  KEY `categorizables_categorizable_type_categorizable_id_index` (`categorizable_type`,`categorizable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `category_price_list`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `category_price_list` (
  `category_id` bigint unsigned NOT NULL,
  `price_list_id` bigint unsigned NOT NULL,
  `discount_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`category_id`,`price_list_id`),
  KEY `category_price_list_price_list_id_foreign` (`price_list_id`),
  KEY `category_price_list_discount_id_foreign` (`discount_id`),
  CONSTRAINT `category_price_list_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE,
  CONSTRAINT `category_price_list_discount_id_foreign` FOREIGN KEY (`discount_id`) REFERENCES `discounts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `category_price_list_price_list_id_foreign` FOREIGN KEY (`price_list_id`) REFERENCES `price_lists` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `client_product`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `client_product` (
  `pivot_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `client_id` bigint unsigned NOT NULL,
  `product_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`pivot_id`),
  KEY `client_product_client_id_foreign` (`client_id`),
  KEY `client_product_product_id_foreign` (`product_id`),
  CONSTRAINT `client_product_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE,
  CONSTRAINT `client_product_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `client_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `client_user` (
  `client_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`client_id`,`user_id`),
  KEY `client_user_user_id_foreign` (`user_id`),
  CONSTRAINT `client_user_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`),
  CONSTRAINT `client_user_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `clients`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `clients` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `country_id` bigint unsigned DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `client_code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ceo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `street` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `postcode` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fax` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `website` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `creditor_identifier` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `vat_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sepa_text` text COLLATE utf8mb4_unicode_ci,
  `opening_hours` json DEFAULT NULL,
  `terms_and_conditions` longtext COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `is_default` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'A timestamp reflecting the time of record-creation.',
  `created_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL COMMENT 'A timestamp reflecting the time of the last change for this record.',
  `updated_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT 'A timestamp reflecting the time of record-deletion.',
  `deleted_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `clients_client_code_unique` (`client_code`),
  KEY `clients_country_id_foreign` (`country_id`),
  CONSTRAINT `clients_country_id_foreign` FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `comments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `comments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  `parent_id` bigint unsigned DEFAULT NULL,
  `comment` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_internal` tinyint(1) NOT NULL DEFAULT '1',
  `is_sticky` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'A timestamp reflecting the time of record-creation.',
  `created_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL COMMENT 'A timestamp reflecting the time of the last change for this record.',
  `updated_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT 'A timestamp reflecting the time of record-deletion.',
  `deleted_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `comments_model_type_model_id_index` (`model_type`,`model_id`),
  KEY `comments_parent_id_foreign` (`parent_id`),
  CONSTRAINT `comments_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `comments` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `commission_rates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `commission_rates` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `contact_id` bigint unsigned DEFAULT NULL,
  `category_id` bigint unsigned DEFAULT NULL,
  `product_id` bigint unsigned DEFAULT NULL,
  `commission_rate` decimal(11,10) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `deleted_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `commission_rates_user_id_foreign` (`user_id`),
  KEY `commission_rates_contact_id_foreign` (`contact_id`),
  KEY `commission_rates_category_id_foreign` (`category_id`),
  KEY `commission_rates_product_id_foreign` (`product_id`),
  CONSTRAINT `commission_rates_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE,
  CONSTRAINT `commission_rates_contact_id_foreign` FOREIGN KEY (`contact_id`) REFERENCES `contacts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `commission_rates_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `commission_rates_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `commissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `commissions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `commission_rate_id` bigint unsigned DEFAULT NULL,
  `order_id` bigint unsigned DEFAULT NULL,
  `order_position_id` bigint unsigned DEFAULT NULL,
  `commission_rate` json NOT NULL,
  `total_net_price` decimal(40,10) NOT NULL,
  `commission` decimal(40,10) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `commissions_user_id_foreign` (`user_id`),
  KEY `commissions_commission_rate_id_foreign` (`commission_rate_id`),
  KEY `commissions_order_id_foreign` (`order_id`),
  KEY `commissions_order_position_id_foreign` (`order_position_id`),
  CONSTRAINT `commissions_commission_rate_id_foreign` FOREIGN KEY (`commission_rate_id`) REFERENCES `commission_rates` (`id`) ON DELETE SET NULL,
  CONSTRAINT `commissions_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL,
  CONSTRAINT `commissions_order_position_id_foreign` FOREIGN KEY (`order_position_id`) REFERENCES `order_positions` (`id`) ON DELETE SET NULL,
  CONSTRAINT `commissions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `communicatable`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `communicatable` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `communicatable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `communicatable_id` bigint unsigned NOT NULL,
  `communication_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `communicatable_type_id_communication_id_unique` (`communicatable_type`,`communicatable_id`,`communication_id`),
  KEY `communicatable_communication_id_foreign` (`communication_id`),
  CONSTRAINT `communicatable_communication_id_foreign` FOREIGN KEY (`communication_id`) REFERENCES `communications` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `communications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `communications` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mail_account_id` bigint unsigned DEFAULT NULL,
  `mail_folder_id` bigint unsigned DEFAULT NULL,
  `message_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `message_uid` int DEFAULT NULL,
  `from` json DEFAULT NULL,
  `to` json DEFAULT NULL,
  `cc` json DEFAULT NULL,
  `bcc` json DEFAULT NULL,
  `communication_type_enum` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `date` datetime DEFAULT NULL,
  `started_at` datetime DEFAULT NULL,
  `ended_at` datetime DEFAULT NULL,
  `total_time_ms` bigint unsigned NOT NULL DEFAULT '0',
  `subject` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `text_body` longtext COLLATE utf8mb4_unicode_ci,
  `html_body` longtext COLLATE utf8mb4_unicode_ci,
  `is_seen` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `deleted_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `communications_mail_account_id_foreign` (`mail_account_id`),
  KEY `communications_mail_folder_id_foreign` (`mail_folder_id`),
  KEY `mail_messages_message_id_index` (`message_id`),
  KEY `mail_messages_message_uid_index` (`message_uid`),
  CONSTRAINT `mail_messages_mail_account_id_foreign` FOREIGN KEY (`mail_account_id`) REFERENCES `mail_accounts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `mail_messages_mail_folder_id_foreign` FOREIGN KEY (`mail_folder_id`) REFERENCES `mail_folders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `contact_bank_connections`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `contact_bank_connections` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `contact_id` bigint unsigned NOT NULL,
  `iban` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `account_holder` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bank_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bic` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'A timestamp reflecting the time of record-creation.',
  `created_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL COMMENT 'A timestamp reflecting the time of the last change for this record.',
  `updated_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT 'A timestamp reflecting the time of record-deletion.',
  `deleted_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `contact_bank_connections_contact_id_foreign` (`contact_id`),
  CONSTRAINT `contact_bank_connections_contact_id_foreign` FOREIGN KEY (`contact_id`) REFERENCES `contacts` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `contact_discount`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `contact_discount` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `contact_id` bigint unsigned NOT NULL,
  `discount_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `contact_discount_contact_id_discount_id_unique` (`contact_id`,`discount_id`),
  KEY `contact_discount_discount_id_foreign` (`discount_id`),
  CONSTRAINT `contact_discount_contact_id_foreign` FOREIGN KEY (`contact_id`) REFERENCES `contacts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `contact_discount_discount_id_foreign` FOREIGN KEY (`discount_id`) REFERENCES `discounts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `contact_discount_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `contact_discount_group` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `contact_id` bigint unsigned NOT NULL,
  `discount_group_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `contact_discount_group_contact_id_discount_group_id_unique` (`contact_id`,`discount_group_id`),
  KEY `contact_discount_group_discount_group_id_foreign` (`discount_group_id`),
  CONSTRAINT `contact_discount_group_contact_id_foreign` FOREIGN KEY (`contact_id`) REFERENCES `contacts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `contact_discount_group_discount_group_id_foreign` FOREIGN KEY (`discount_group_id`) REFERENCES `discount_groups` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `contact_options`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `contact_options` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `address_id` bigint unsigned NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `label` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'A timestamp reflecting the time of record-creation.',
  `created_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL COMMENT 'A timestamp reflecting the time of the last change for this record.',
  `updated_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `contact_options_address_id_foreign` (`address_id`),
  CONSTRAINT `contact_options_address_id_foreign` FOREIGN KEY (`address_id`) REFERENCES `addresses` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `contact_origins`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `contact_origins` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `contacts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `contacts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `approval_user_id` bigint unsigned DEFAULT NULL,
  `payment_type_id` bigint unsigned DEFAULT NULL,
  `purchase_payment_type_id` bigint unsigned DEFAULT NULL,
  `price_list_id` bigint unsigned DEFAULT NULL,
  `client_id` bigint unsigned NOT NULL,
  `agent_id` bigint unsigned DEFAULT NULL,
  `contact_origin_id` bigint unsigned DEFAULT NULL,
  `currency_id` bigint unsigned DEFAULT NULL,
  `expense_ledger_account_id` bigint unsigned DEFAULT NULL,
  `main_address_id` bigint unsigned DEFAULT NULL,
  `invoice_address_id` bigint unsigned DEFAULT NULL,
  `delivery_address_id` bigint unsigned DEFAULT NULL,
  `vat_rate_id` bigint unsigned DEFAULT NULL,
  `customer_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `creditor_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `debtor_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_target_days` int DEFAULT NULL,
  `payment_reminder_days_1` int DEFAULT NULL,
  `payment_reminder_days_2` int DEFAULT NULL,
  `payment_reminder_days_3` int DEFAULT NULL,
  `discount_days` int DEFAULT NULL,
  `discount_percent` double DEFAULT NULL,
  `credit_line` decimal(8,2) DEFAULT NULL,
  `vat_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `vendor_customer_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `has_sensitive_reminder` tinyint(1) NOT NULL DEFAULT '0',
  `has_delivery_lock` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'A timestamp reflecting the time of record-creation.',
  `created_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL COMMENT 'A timestamp reflecting the time of the last change for this record.',
  `updated_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT 'A timestamp reflecting the time of record-deletion.',
  `deleted_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `contacts_customer_number_client_id_unique` (`customer_number`,`client_id`),
  UNIQUE KEY `contacts_creditor_number_client_id_unique` (`creditor_number`,`client_id`),
  KEY `contacts_payment_type_id_foreign` (`payment_type_id`),
  KEY `contacts_client_id_foreign` (`client_id`),
  KEY `contacts_approval_user_id_foreign` (`approval_user_id`),
  KEY `contacts_expense_ledger_account_id_foreign` (`expense_ledger_account_id`),
  KEY `contacts_vat_rate_id_foreign` (`vat_rate_id`),
  KEY `contacts_agent_id_foreign` (`agent_id`),
  KEY `contacts_main_address_id_foreign` (`main_address_id`),
  KEY `contacts_invoice_address_id_foreign` (`invoice_address_id`),
  KEY `contacts_delivery_address_id_foreign` (`delivery_address_id`),
  KEY `contacts_purchase_payment_type_id_foreign` (`purchase_payment_type_id`),
  KEY `contacts_currency_id_foreign` (`currency_id`),
  KEY `contacts_contact_origin_id_foreign` (`contact_origin_id`),
  CONSTRAINT `contacts_agent_id_foreign` FOREIGN KEY (`agent_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `contacts_approval_user_id_foreign` FOREIGN KEY (`approval_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `contacts_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`),
  CONSTRAINT `contacts_contact_origin_id_foreign` FOREIGN KEY (`contact_origin_id`) REFERENCES `contact_origins` (`id`) ON DELETE SET NULL,
  CONSTRAINT `contacts_currency_id_foreign` FOREIGN KEY (`currency_id`) REFERENCES `currencies` (`id`) ON DELETE SET NULL,
  CONSTRAINT `contacts_delivery_address_id_foreign` FOREIGN KEY (`delivery_address_id`) REFERENCES `addresses` (`id`) ON DELETE SET NULL,
  CONSTRAINT `contacts_expense_ledger_account_id_foreign` FOREIGN KEY (`expense_ledger_account_id`) REFERENCES `ledger_accounts` (`id`) ON DELETE SET NULL,
  CONSTRAINT `contacts_invoice_address_id_foreign` FOREIGN KEY (`invoice_address_id`) REFERENCES `addresses` (`id`) ON DELETE SET NULL,
  CONSTRAINT `contacts_main_address_id_foreign` FOREIGN KEY (`main_address_id`) REFERENCES `addresses` (`id`) ON DELETE SET NULL,
  CONSTRAINT `contacts_payment_type_id_foreign` FOREIGN KEY (`payment_type_id`) REFERENCES `payment_types` (`id`),
  CONSTRAINT `contacts_purchase_payment_type_id_foreign` FOREIGN KEY (`purchase_payment_type_id`) REFERENCES `payment_types` (`id`) ON DELETE SET NULL,
  CONSTRAINT `contacts_vat_rate_id_foreign` FOREIGN KEY (`vat_rate_id`) REFERENCES `vat_rates` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `countries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `countries` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `language_id` bigint unsigned NOT NULL,
  `currency_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `iso_alpha2` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `iso_alpha3` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `iso_numeric` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `is_default` tinyint(1) NOT NULL DEFAULT '0',
  `is_eu_country` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'A timestamp reflecting the time of record-creation.',
  `created_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL COMMENT 'A timestamp reflecting the time of the last change for this record.',
  `updated_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT 'A timestamp reflecting the time of record-deletion.',
  `deleted_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `countries_iso_alpha2_unique` (`iso_alpha2`),
  KEY `countries_language_id_foreign` (`language_id`),
  KEY `countries_currency_id_foreign` (`currency_id`),
  CONSTRAINT `countries_currency_id_foreign` FOREIGN KEY (`currency_id`) REFERENCES `currencies` (`id`),
  CONSTRAINT `countries_language_id_foreign` FOREIGN KEY (`language_id`) REFERENCES `languages` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `country_regions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `country_regions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `country_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'A timestamp reflecting the time of record-creation.',
  `created_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL COMMENT 'A timestamp reflecting the time of the last change for this record.',
  `updated_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT 'A timestamp reflecting the time of record-deletion.',
  `deleted_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `country_regions_country_id_foreign` (`country_id`),
  CONSTRAINT `country_regions_country_id_foreign` FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `currencies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `currencies` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `iso` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `symbol` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_default` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'A timestamp reflecting the time of record-creation.',
  `created_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL COMMENT 'A timestamp reflecting the time of the last change for this record.',
  `updated_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT 'A timestamp reflecting the time of record-deletion.',
  `deleted_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `currencies_iso_unique` (`iso`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `custom_events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `custom_events` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `model_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `custom_events_name_unique` (`name`),
  KEY `custom_events_model_type_model_id_index` (`model_type`,`model_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `datatable_user_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `datatable_user_settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cache_key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `component` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `settings` json NOT NULL,
  `authenticatable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `authenticatable_id` bigint unsigned NOT NULL,
  `is_layout` tinyint(1) NOT NULL DEFAULT '0',
  `is_permanent` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `datatable_authenticatable` (`authenticatable_type`,`authenticatable_id`),
  KEY `datatable_user_settings_component_index` (`component`),
  KEY `datatable_user_settings_cache_key_index` (`cache_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `datev_client_setting_payment_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `datev_client_setting_payment_type` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `datev_client_setting_id` bigint unsigned NOT NULL,
  `payment_type_id` bigint unsigned NOT NULL,
  `ledger_account_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `datev_client_setting_id` (`datev_client_setting_id`),
  KEY `datev_client_setting_payment_type` (`payment_type_id`),
  KEY `datev_client_setting_payment_type_ledger_account_id_foreign` (`ledger_account_id`),
  CONSTRAINT `datev_client_setting_id` FOREIGN KEY (`datev_client_setting_id`) REFERENCES `datev_client_settings` (`id`) ON DELETE CASCADE,
  CONSTRAINT `datev_client_setting_payment_type` FOREIGN KEY (`payment_type_id`) REFERENCES `payment_types` (`id`) ON DELETE CASCADE,
  CONSTRAINT `datev_client_setting_payment_type_ledger_account_id_foreign` FOREIGN KEY (`ledger_account_id`) REFERENCES `ledger_accounts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `datev_client_setting_vat_rate`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `datev_client_setting_vat_rate` (
  `datev_client_setting_id` bigint unsigned NOT NULL,
  `vat_rate_id` bigint unsigned NOT NULL,
  `revenue_ledger_account_id` bigint unsigned DEFAULT NULL,
  `expense_ledger_account_id` bigint unsigned DEFAULT NULL,
  `expense_posting_key` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `revenue_posting_key` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  UNIQUE KEY `datev_client_setting_vat_rates_unique` (`datev_client_setting_id`,`vat_rate_id`),
  KEY `datev_client_setting_vat_rate_vat_rate_id_foreign` (`vat_rate_id`),
  KEY `datev_client_setting_vat_rate_expense_ledger_account_id_foreign` (`expense_ledger_account_id`),
  KEY `datev_client_setting_vat_rate_revenue_ledger_account_id_foreign` (`revenue_ledger_account_id`),
  CONSTRAINT `datev_client_setting_vat_rate_datev_client_setting_id_foreign` FOREIGN KEY (`datev_client_setting_id`) REFERENCES `datev_client_settings` (`id`),
  CONSTRAINT `datev_client_setting_vat_rate_expense_ledger_account_id_foreign` FOREIGN KEY (`expense_ledger_account_id`) REFERENCES `ledger_accounts` (`id`) ON DELETE SET NULL,
  CONSTRAINT `datev_client_setting_vat_rate_revenue_ledger_account_id_foreign` FOREIGN KEY (`revenue_ledger_account_id`) REFERENCES `ledger_accounts` (`id`) ON DELETE SET NULL,
  CONSTRAINT `datev_client_setting_vat_rate_vat_rate_id_foreign` FOREIGN KEY (`vat_rate_id`) REFERENCES `vat_rates` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `datev_client_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `datev_client_settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `client_id` bigint unsigned NOT NULL,
  `account_length` int NOT NULL DEFAULT '4',
  `notification_email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `client_number` int NOT NULL,
  `advisor_number` int NOT NULL,
  `fiscal_year_start_month` int NOT NULL DEFAULT '1',
  `fiscal_year_start_day` int NOT NULL DEFAULT '1',
  `export_by_column` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'invoice_date',
  `is_fibu_transfer` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `datev_client_settings_client_id_unique` (`client_id`),
  CONSTRAINT `datev_client_settings_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `datev_export_lines`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `datev_export_lines` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `order_id` bigint unsigned NOT NULL,
  `invoice_id` bigint unsigned NOT NULL,
  `datev_export_id` bigint unsigned DEFAULT NULL,
  `is_blacklisted` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `datev_export_lines_order_id_foreign` (`order_id`),
  KEY `datev_export_lines_invoice_id_foreign` (`invoice_id`),
  KEY `datev_export_lines_datev_export_id_foreign` (`datev_export_id`),
  CONSTRAINT `datev_export_lines_datev_export_id_foreign` FOREIGN KEY (`datev_export_id`) REFERENCES `datev_exports` (`id`) ON DELETE SET NULL,
  CONSTRAINT `datev_export_lines_invoice_id_foreign` FOREIGN KEY (`invoice_id`) REFERENCES `media` (`id`) ON DELETE CASCADE,
  CONSTRAINT `datev_export_lines_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `datev_exports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `datev_exports` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `client_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `booking_month_from` date NOT NULL,
  `booking_month_to` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `datev_exports_client_id_foreign` (`client_id`),
  CONSTRAINT `datev_exports_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `discount_discount_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `discount_discount_group` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `discount_id` bigint unsigned NOT NULL,
  `discount_group_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `discount_discount_group_discount_id_foreign` (`discount_id`),
  KEY `discount_discount_group_discount_group_id_foreign` (`discount_group_id`),
  CONSTRAINT `discount_discount_group_discount_group_id_foreign` FOREIGN KEY (`discount_group_id`) REFERENCES `discount_groups` (`id`) ON DELETE CASCADE,
  CONSTRAINT `discount_discount_group_discount_id_foreign` FOREIGN KEY (`discount_id`) REFERENCES `discounts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `discount_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `discount_groups` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `discounts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `discounts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT 'An incrementing number to uniquely identify a record in this table. This also is the primary key of this table.',
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'A 36 character long unique identifier string for a record within the whole application.',
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  `discount` decimal(40,10) NOT NULL COMMENT 'The number containing the actual discount.',
  `from` timestamp NULL DEFAULT NULL,
  `till` timestamp NULL DEFAULT NULL,
  `sort_number` int unsigned DEFAULT NULL COMMENT 'A number containing the position in the sequence of multiple discounts for one order-position.',
  `is_percentage` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'A boolean deciding if this discount is a percentage instead of a discount in its respective currency.',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'A timestamp reflecting the time of record-creation.',
  `created_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL COMMENT 'A timestamp reflecting the time of the last change for this record.',
  `updated_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT 'A timestamp reflecting the time of record-deletion.',
  `deleted_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `discounts_model_type_model_id_index` (`model_type`,`model_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `document_generation_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `document_generation_settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `client_id` bigint unsigned NOT NULL,
  `document_type_id` bigint unsigned NOT NULL,
  `order_type_id` bigint unsigned NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '0',
  `is_generation_preset` tinyint(1) NOT NULL DEFAULT '0',
  `is_generation_forced` tinyint(1) NOT NULL DEFAULT '0',
  `is_print_preset` tinyint(1) NOT NULL DEFAULT '0',
  `is_print_forced` tinyint(1) NOT NULL DEFAULT '0',
  `is_email_preset` tinyint(1) NOT NULL DEFAULT '0',
  `is_email_forced` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'A timestamp reflecting the time of record-creation.',
  `created_by` bigint unsigned DEFAULT NULL COMMENT 'A unique identifier number for the table users of the user that created this record.',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT 'A timestamp reflecting the time of the last change for this record.',
  `updated_by` bigint unsigned DEFAULT NULL COMMENT 'A unique identifier number for the table users of the user that changed this record last.',
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT 'A timestamp reflecting the time of record-deletion.',
  `deleted_by` bigint unsigned DEFAULT NULL COMMENT 'A unique identifier number for the table users of the user that deleted this record.',
  PRIMARY KEY (`id`),
  KEY `document_generation_settings_client_id_foreign` (`client_id`),
  KEY `document_generation_settings_document_type_id_foreign` (`document_type_id`),
  KEY `document_generation_settings_order_type_id_foreign` (`order_type_id`),
  KEY `document_generation_settings_created_by_foreign` (`created_by`),
  KEY `document_generation_settings_updated_by_foreign` (`updated_by`),
  KEY `document_generation_settings_deleted_by_foreign` (`deleted_by`),
  CONSTRAINT `document_generation_settings_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`),
  CONSTRAINT `document_generation_settings_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  CONSTRAINT `document_generation_settings_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`),
  CONSTRAINT `document_generation_settings_document_type_id_foreign` FOREIGN KEY (`document_type_id`) REFERENCES `document_types` (`id`),
  CONSTRAINT `document_generation_settings_order_type_id_foreign` FOREIGN KEY (`order_type_id`) REFERENCES `order_types` (`id`),
  CONSTRAINT `document_generation_settings_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `document_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `document_types` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `client_id` bigint unsigned NOT NULL,
  `name` json NOT NULL,
  `description` json DEFAULT NULL,
  `additional_header` json DEFAULT NULL,
  `additional_footer` json DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'A timestamp reflecting the time of record-creation.',
  `created_by` bigint unsigned DEFAULT NULL COMMENT 'A unique identifier number for the table users of the user that created this record.',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT 'A timestamp reflecting the time of the last change for this record.',
  `updated_by` bigint unsigned DEFAULT NULL COMMENT 'A unique identifier number for the table users of the user that changed this record last.',
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT 'A timestamp reflecting the time of record-deletion.',
  `deleted_by` bigint unsigned DEFAULT NULL COMMENT 'A unique identifier number for the table users of the user that deleted this record.',
  PRIMARY KEY (`id`),
  KEY `document_types_client_id_foreign` (`client_id`),
  KEY `document_types_created_by_foreign` (`created_by`),
  KEY `document_types_updated_by_foreign` (`updated_by`),
  KEY `document_types_deleted_by_foreign` (`deleted_by`),
  CONSTRAINT `document_types_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`),
  CONSTRAINT `document_types_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  CONSTRAINT `document_types_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`),
  CONSTRAINT `document_types_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `email_templates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `email_templates` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `from` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `from_alias` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `to` json DEFAULT NULL,
  `cc` json DEFAULT NULL,
  `bcc` json DEFAULT NULL,
  `subject` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `message` text COLLATE utf8mb4_unicode_ci,
  `view` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `view_data` json DEFAULT NULL,
  `can_overwrite_message` tinyint(1) NOT NULL DEFAULT '1',
  `can_overwrite_receiver` tinyint(1) NOT NULL DEFAULT '1',
  `can_overwrite_sender` tinyint(1) NOT NULL DEFAULT '1',
  `can_overwrite_subject` tinyint(1) NOT NULL DEFAULT '1',
  `can_overwrite_view` tinyint(1) NOT NULL DEFAULT '0',
  `should_prohibit_release` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'A timestamp reflecting the time of record-creation.',
  `created_by` bigint unsigned DEFAULT NULL COMMENT 'A unique identifier number for the table users of the user that created this record.',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT 'A timestamp reflecting the time of the last change for this record.',
  `updated_by` bigint unsigned DEFAULT NULL COMMENT 'A unique identifier number for the table users of the user that changed this record last.',
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT 'A timestamp reflecting the time of record-deletion.',
  `deleted_by` bigint unsigned DEFAULT NULL COMMENT 'A unique identifier number for the table users of the user that deleted this record.',
  PRIMARY KEY (`id`),
  KEY `email_templates_created_by_foreign` (`created_by`),
  KEY `email_templates_updated_by_foreign` (`updated_by`),
  KEY `email_templates_deleted_by_foreign` (`deleted_by`),
  CONSTRAINT `email_templates_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  CONSTRAINT `email_templates_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`),
  CONSTRAINT `email_templates_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `emails`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `emails` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_template_id` bigint unsigned NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `model_id` bigint unsigned DEFAULT NULL,
  `from` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `from_alias` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `to` json NOT NULL,
  `cc` json DEFAULT NULL,
  `bcc` json DEFAULT NULL,
  `subject` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `message` text COLLATE utf8mb4_unicode_ci,
  `view` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `view_data` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'A timestamp reflecting the time of record-creation.',
  `created_by` bigint unsigned DEFAULT NULL COMMENT 'A unique identifier number for the table users of the user that created this record.',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT 'A timestamp reflecting the time of the last change for this record.',
  `updated_by` bigint unsigned DEFAULT NULL COMMENT 'A unique identifier number for the table users of the user that changed this record last.',
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT 'A timestamp reflecting the time of record-deletion.',
  `deleted_by` bigint unsigned DEFAULT NULL COMMENT 'A unique identifier number for the table users of the user that deleted this record.',
  PRIMARY KEY (`id`),
  KEY `emails_model_type_model_id_index` (`model_type`,`model_id`),
  KEY `emails_email_template_id_foreign` (`email_template_id`),
  KEY `emails_created_by_foreign` (`created_by`),
  KEY `emails_updated_by_foreign` (`updated_by`),
  KEY `emails_deleted_by_foreign` (`deleted_by`),
  CONSTRAINT `emails_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  CONSTRAINT `emails_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`),
  CONSTRAINT `emails_email_template_id_foreign` FOREIGN KEY (`email_template_id`) REFERENCES `email_templates` (`id`),
  CONSTRAINT `emails_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `event_subscriptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `event_subscriptions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `event` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subscribable_id` bigint unsigned NOT NULL,
  `subscribable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned DEFAULT NULL,
  `is_broadcast` tinyint(1) NOT NULL DEFAULT '0',
  `is_notifiable` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `event_notifications_event_index` (`event`),
  KEY `event_notifications_model_type_index` (`model_type`),
  KEY `event_notifications_model_id_index` (`model_id`),
  KEY `event_subscriptions_subscribable_id_subscribable_type_index` (`subscribable_id`,`subscribable_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `favorites`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `favorites` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `authenticatable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `authenticatable_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `favorites_uuid_unique` (`uuid`),
  KEY `favorites_authenticatable_type_authenticatable_id_index` (`authenticatable_type`,`authenticatable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `form_builder_field_responses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `form_builder_field_responses` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `form_id` bigint unsigned NOT NULL,
  `field_id` bigint unsigned NOT NULL,
  `response_id` bigint unsigned NOT NULL,
  `response` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `form_builder_field_responses_form_id_foreign` (`form_id`),
  KEY `form_builder_field_responses_field_id_foreign` (`field_id`),
  KEY `form_builder_field_responses_response_id_foreign` (`response_id`),
  CONSTRAINT `form_builder_field_responses_field_id_foreign` FOREIGN KEY (`field_id`) REFERENCES `form_builder_fields` (`id`) ON DELETE CASCADE,
  CONSTRAINT `form_builder_field_responses_form_id_foreign` FOREIGN KEY (`form_id`) REFERENCES `form_builder_forms` (`id`) ON DELETE CASCADE,
  CONSTRAINT `form_builder_field_responses_response_id_foreign` FOREIGN KEY (`response_id`) REFERENCES `form_builder_responses` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `form_builder_fields`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `form_builder_fields` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `section_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ordering` int unsigned NOT NULL DEFAULT '0',
  `options` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `form_builder_fields_section_id_foreign` (`section_id`),
  CONSTRAINT `form_builder_fields_section_id_foreign` FOREIGN KEY (`section_id`) REFERENCES `form_builder_sections` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `form_builder_forms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `form_builder_forms` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `model_id` bigint unsigned DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` json DEFAULT NULL,
  `start_date` datetime DEFAULT NULL,
  `end_date` datetime DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `form_builder_forms_model_type_model_id_index` (`model_type`,`model_id`),
  KEY `form_builder_forms_user_id_foreign` (`user_id`),
  CONSTRAINT `form_builder_forms_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `form_builder_responses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `form_builder_responses` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `form_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `form_builder_responses_form_id_foreign` (`form_id`),
  KEY `form_builder_responses_user_id_foreign` (`user_id`),
  CONSTRAINT `form_builder_responses_form_id_foreign` FOREIGN KEY (`form_id`) REFERENCES `form_builder_forms` (`id`) ON DELETE CASCADE,
  CONSTRAINT `form_builder_responses_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `form_builder_sections`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `form_builder_sections` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `form_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `ordering` int unsigned NOT NULL DEFAULT '0',
  `columns` int unsigned NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `form_builder_sections_form_id_foreign` (`form_id`),
  CONSTRAINT `form_builder_sections_form_id_foreign` FOREIGN KEY (`form_id`) REFERENCES `form_builder_forms` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `interface_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `interface_users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'A timestamp reflecting the time of record-creation.',
  `created_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL COMMENT 'A timestamp reflecting the time of the last change for this record.',
  `updated_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT 'A timestamp reflecting the time of record-deletion.',
  `deleted_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `interface_users_name_unique` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `inviteables`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `inviteables` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `calendar_event_id` bigint unsigned NOT NULL,
  `model_calendar_id` bigint unsigned DEFAULT NULL,
  `inviteable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `inviteable_id` bigint unsigned NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `inviteables_unique` (`calendar_event_id`,`inviteable_id`,`inviteable_type`),
  KEY `inviteables_inviteable_type_inviteable_id_index` (`inviteable_type`,`inviteable_id`),
  KEY `inviteables_model_calendar_id_foreign` (`model_calendar_id`),
  CONSTRAINT `inviteables_calendar_event_id_foreign` FOREIGN KEY (`calendar_event_id`) REFERENCES `calendar_events` (`id`) ON DELETE CASCADE,
  CONSTRAINT `inviteables_model_calendar_id_foreign` FOREIGN KEY (`model_calendar_id`) REFERENCES `calendars` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `job_batchables`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_batchables` (
  `job_batch_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `job_batchable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `job_batchable_id` bigint unsigned NOT NULL,
  `notify_on_finish` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`job_batch_id`,`job_batchable_id`,`job_batchable_type`),
  KEY `job_batchable_index` (`job_batchable_type`,`job_batchable_id`),
  CONSTRAINT `job_batchables_job_batch_id_foreign` FOREIGN KEY (`job_batch_id`) REFERENCES `job_batches` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `language_lines`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `language_lines` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `group` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `text` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `language_lines_group_index` (`group`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `languages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `languages` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `iso_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `language_code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_default` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'A timestamp reflecting the time of record-creation.',
  `created_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL COMMENT 'A timestamp reflecting the time of the last change for this record.',
  `updated_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT 'A timestamp reflecting the time of record-deletion.',
  `deleted_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `languages_language_code_unique` (`language_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ledger_accounts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ledger_accounts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ledger_account_type_enum` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_automatic` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ledger_accounts_number_unique` (`number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `locks` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  `authenticatable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `authenticatable_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'A timestamp reflecting the time of record-creation.',
  `created_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL COMMENT 'A timestamp reflecting the time of the last change for this record.',
  `updated_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `locks_model_type_model_id_unique` (`model_type`,`model_id`),
  KEY `locks_model_type_model_id_index` (`model_type`,`model_id`),
  KEY `locks_authenticatable_type_authenticatable_id_index` (`authenticatable_type`,`authenticatable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `foreign_uuid` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `message` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `context` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `level` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `level_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `channel` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `record_datetime` datetime NOT NULL,
  `extra` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `formatted` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_done` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `logs_foreign_uuid_index` (`foreign_uuid`),
  KEY `logs_level_index` (`level`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mail_account_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mail_account_user` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `mail_account_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `mail_account_user_user_id_foreign` (`user_id`),
  KEY `mail_account_user_mail_account_id_foreign` (`mail_account_id`),
  CONSTRAINT `mail_account_user_mail_account_id_foreign` FOREIGN KEY (`mail_account_id`) REFERENCES `mail_accounts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `mail_account_user_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mail_accounts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mail_accounts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `protocol` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'imap',
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `host` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `port` int NOT NULL DEFAULT '993',
  `encryption` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ssl',
  `smtp_mailer` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'smtp',
  `smtp_email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `smtp_password` text COLLATE utf8mb4_unicode_ci,
  `smtp_host` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `smtp_port` int NOT NULL DEFAULT '587',
  `smtp_encryption` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_auto_assign` tinyint(1) NOT NULL DEFAULT '0',
  `is_o_auth` tinyint(1) NOT NULL DEFAULT '0',
  `has_valid_certificate` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mail_accounts_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mail_folders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mail_folders` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mail_account_id` bigint unsigned NOT NULL,
  `parent_id` bigint unsigned DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `can_create_ticket` tinyint(1) NOT NULL DEFAULT '0',
  `can_create_purchase_invoice` tinyint(1) NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `mail_folders_mail_account_id_foreign` (`mail_account_id`),
  KEY `mail_folders_parent_id_foreign` (`parent_id`),
  CONSTRAINT `mail_folders_mail_account_id_foreign` FOREIGN KEY (`mail_account_id`) REFERENCES `mail_accounts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `mail_folders_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `mail_folders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `media`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `media` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  `parent_id` bigint unsigned DEFAULT NULL,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `collection_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mime_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `disk` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `conversions_disk` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `size` bigint unsigned NOT NULL,
  `manipulations` json NOT NULL,
  `custom_properties` json NOT NULL,
  `generated_conversions` json NOT NULL,
  `responsive_images` json NOT NULL,
  `order_column` int unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `media_uuid_unique` (`uuid`),
  KEY `media_model_type_model_id_index` (`model_type`,`model_id`),
  KEY `media_parent_id_foreign` (`parent_id`),
  CONSTRAINT `media_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `media` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `meta`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `meta` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `additional_column_id` bigint unsigned DEFAULT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  `key` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `value` longtext COLLATE utf8mb4_unicode_ci,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `meta_additional_column_id_foreign` (`additional_column_id`),
  KEY `meta_model_type_model_id_index` (`model_type`,`model_id`),
  CONSTRAINT `meta_additional_column_id_foreign` FOREIGN KEY (`additional_column_id`) REFERENCES `additional_columns` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `model_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `model_has_permissions` (
  `permission_id` bigint unsigned NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `model_has_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `model_has_roles` (
  `role_id` bigint unsigned NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `model_related`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `model_related` (
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  `related_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `related_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`model_type`,`model_id`,`related_type`,`related_id`),
  KEY `model_related_model_type_model_id_index` (`model_type`,`model_id`),
  KEY `model_related_related_type_related_id_index` (`related_type`,`related_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `notification_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notification_settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `notifiable_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notifiable_id` bigint unsigned DEFAULT NULL,
  `notification_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `channel` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `channel_value` json DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `notification_settings_unique` (`notifiable_id`,`notifiable_type`,`notification_type`,`channel`),
  KEY `notification_settings_notifiable_type_notifiable_id_index` (`notifiable_type`,`notifiable_id`),
  KEY `notification_settings_notification_type_index` (`notification_type`),
  KEY `notification_settings_channel_index` (`channel`),
  KEY `notification_settings_is_active_index` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notifications` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notifiable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notifiable_id` bigint unsigned NOT NULL,
  `data` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notifications_notifiable_type_notifiable_id_index` (`notifiable_type`,`notifiable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `open_ai_assistants`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `open_ai_assistants` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `authenticatable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `assistant_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `model` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `instructions` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `open_ai_assistants_authenticatable_type_index` (`authenticatable_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `open_ai_files`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `open_ai_files` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `file_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `purpose` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_uploaded` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `open_ai_messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `open_ai_messages` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `open_ai_thread_id` bigint unsigned NOT NULL,
  `message_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `response` json NOT NULL,
  `message_text` longtext COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `open_ai_messages_open_ai_thread_id_foreign` (`open_ai_thread_id`),
  CONSTRAINT `open_ai_messages_open_ai_thread_id_foreign` FOREIGN KEY (`open_ai_thread_id`) REFERENCES `open_ai_threads` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `open_ai_runs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `open_ai_runs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `open_ai_thread_id` bigint unsigned NOT NULL,
  `run_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `open_ai_runs_open_ai_thread_id_foreign` (`open_ai_thread_id`),
  CONSTRAINT `open_ai_runs_chat_id_foreign` FOREIGN KEY (`open_ai_thread_id`) REFERENCES `open_ai_threads` (`id`),
  CONSTRAINT `open_ai_runs_open_ai_thread_id_foreign` FOREIGN KEY (`open_ai_thread_id`) REFERENCES `open_ai_threads` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `open_ai_threads`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `open_ai_threads` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `open_ai_assistant_id` bigint unsigned DEFAULT NULL,
  `authenticatable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `authenticatable_id` bigint unsigned NOT NULL,
  `thread_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `open_ai_threads_open_ai_assistant_id_foreign` (`open_ai_assistant_id`),
  CONSTRAINT `open_ai_threads_open_ai_assistant_id_foreign` FOREIGN KEY (`open_ai_assistant_id`) REFERENCES `open_ai_assistants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `order_payment_run`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `order_payment_run` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint unsigned NOT NULL,
  `payment_run_id` bigint unsigned NOT NULL,
  `amount` decimal(40,10) NOT NULL,
  `success` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `order_payment_run_order_id_foreign` (`order_id`),
  KEY `order_payment_run_payment_run_id_foreign` (`payment_run_id`),
  CONSTRAINT `order_payment_run_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_payment_run_payment_run_id_foreign` FOREIGN KEY (`payment_run_id`) REFERENCES `payment_runs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `order_position_task`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `order_position_task` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `order_position_id` bigint unsigned NOT NULL,
  `task_id` bigint unsigned NOT NULL,
  `amount` decimal(40,10) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `order_position_task_order_position_id_foreign` (`order_position_id`),
  KEY `order_position_task_task_id_foreign` (`task_id`),
  CONSTRAINT `order_position_task_order_position_id_foreign` FOREIGN KEY (`order_position_id`) REFERENCES `order_positions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_position_task_task_id_foreign` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `order_positions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `order_positions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `client_id` bigint unsigned NOT NULL COMMENT 'A unique identifier number for the table clients.',
  `ledger_account_id` bigint unsigned DEFAULT NULL,
  `order_id` bigint unsigned NOT NULL,
  `origin_position_id` bigint unsigned DEFAULT NULL,
  `parent_id` bigint unsigned DEFAULT NULL COMMENT 'A unique identifier number for the table order_positions. This connects this position to one parent-position.',
  `price_id` bigint unsigned DEFAULT NULL COMMENT 'A unique identifier number for the table prices.',
  `price_list_id` bigint unsigned DEFAULT NULL COMMENT 'A unique identifier number for the table price_lists.',
  `product_id` bigint unsigned DEFAULT NULL COMMENT 'A unique identifier number for the table products.',
  `supplier_contact_id` bigint unsigned DEFAULT NULL COMMENT 'A unique identifier number for the table contacts.',
  `vat_rate_id` bigint unsigned DEFAULT NULL COMMENT 'A unique identifier number for the table vat_rates.',
  `warehouse_id` bigint unsigned DEFAULT NULL COMMENT 'A unique identifier number for the table warehouses.',
  `amount` decimal(40,10) DEFAULT NULL COMMENT 'A decimal containing the accurate amount this order-position uses of its associated product.',
  `amount_bundle` decimal(40,10) DEFAULT NULL,
  `discount_percentage` decimal(11,10) DEFAULT NULL,
  `margin` decimal(40,10) DEFAULT NULL COMMENT 'A decimal containing the cached margin calculated for this order-position.',
  `provision` decimal(40,10) DEFAULT NULL COMMENT 'A decimal containing the provision for this order-position.',
  `purchase_price` decimal(40,10) DEFAULT NULL COMMENT 'A decimal containing the purchase price per unit of the associated product. This gets calculated from stock-postings.',
  `total_base_gross_price` decimal(40,10) DEFAULT NULL COMMENT 'A decimal containing the order-position total price before any discounts. Can be net or gross depending on the field is_net.',
  `total_base_net_price` decimal(40,10) DEFAULT NULL COMMENT 'A decimal containing the order-position total price before any discounts. Can be net or gross depending on the field is_net.',
  `total_gross_price` decimal(40,10) DEFAULT NULL COMMENT 'A decimal containing the order-position total price gross after all calculations.',
  `total_net_price` decimal(40,10) DEFAULT NULL COMMENT 'A decimal containing the order-position total price gross after all calculations.',
  `vat_price` decimal(40,10) DEFAULT NULL COMMENT 'A decimal containing the tax for this order-position.',
  `unit_net_price` decimal(40,10) DEFAULT NULL COMMENT 'A decimal containing the price per unit of the associated product.',
  `unit_gross_price` decimal(40,10) DEFAULT NULL COMMENT 'A decimal containing the price per unit net of the associated product.',
  `vat_rate_percentage` decimal(40,10) DEFAULT NULL COMMENT 'A decimal, containing the vat-rate in percent, that is cached for easier and faster readability of this order-position.',
  `amount_packed_products` decimal(40,10) DEFAULT NULL COMMENT 'A decimal containing the amount of packed products for this order-position.',
  `customer_delivery_date` date DEFAULT NULL COMMENT 'A date containing the delivery date desired by the customer for this order-position.',
  `ean_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'A number containing the European Article Number for the associated product for this order-position.',
  `possible_delivery_date` date DEFAULT NULL COMMENT 'A date containing the earliest possible delivery date for this order-position.',
  `unit_gram_weight` decimal(40,10) DEFAULT NULL COMMENT 'A decimal containing the weight per unit for the associated product for this order-position in grams.',
  `description` text COLLATE utf8mb4_unicode_ci COMMENT 'A string containing a description for the current order-position. Normally this text contains the associated product-description',
  `name` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'A string containing a descriptive name or text for the current order-position. Normally this text is set to the associated product-name',
  `product_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'A string containing the associated product-number.',
  `product_prices` json DEFAULT NULL,
  `sort_number` int unsigned NOT NULL COMMENT 'A number to determine the order-positions position in the order.',
  `is_alternative` tinyint(1) NOT NULL DEFAULT '0',
  `is_net` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'A boolean deciding if this order-position is calculated in net instead of gross.',
  `is_free_text` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'A boolean deciding if this order-position is just free text instead of having a product associated.',
  `is_bundle_position` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'A timestamp reflecting the time of record-creation.',
  `created_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL COMMENT 'A timestamp reflecting the time of the last change for this record.',
  `updated_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT 'A timestamp reflecting the time of record-deletion.',
  `deleted_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `order_positions_client_id_foreign` (`client_id`),
  KEY `order_positions_product_id_foreign` (`product_id`),
  KEY `order_positions_supplier_contact_id_foreign` (`supplier_contact_id`),
  KEY `order_positions_vat_rate_id_foreign` (`vat_rate_id`),
  KEY `order_positions_warehouse_id_foreign` (`warehouse_id`),
  KEY `order_positions_price_list_id_foreign` (`price_list_id`),
  KEY `order_positions_ledger_account_id_foreign` (`ledger_account_id`),
  KEY `order_positions_parent_id_foreign` (`parent_id`),
  KEY `order_positions_order_id_foreign` (`order_id`),
  KEY `order_positions_origin_position_id_foreign` (`origin_position_id`),
  CONSTRAINT `order_positions_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`),
  CONSTRAINT `order_positions_ledger_account_id_foreign` FOREIGN KEY (`ledger_account_id`) REFERENCES `ledger_accounts` (`id`) ON DELETE SET NULL,
  CONSTRAINT `order_positions_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_positions_origin_position_id_foreign` FOREIGN KEY (`origin_position_id`) REFERENCES `order_positions` (`id`) ON DELETE SET NULL,
  CONSTRAINT `order_positions_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `order_positions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_positions_price_list_id_foreign` FOREIGN KEY (`price_list_id`) REFERENCES `price_lists` (`id`),
  CONSTRAINT `order_positions_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  CONSTRAINT `order_positions_supplier_contact_id_foreign` FOREIGN KEY (`supplier_contact_id`) REFERENCES `contacts` (`id`),
  CONSTRAINT `order_positions_vat_rate_id_foreign` FOREIGN KEY (`vat_rate_id`) REFERENCES `vat_rates` (`id`),
  CONSTRAINT `order_positions_warehouse_id_foreign` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `order_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `order_types` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `client_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mail_subject` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mail_body` text COLLATE utf8mb4_unicode_ci,
  `print_layouts` json DEFAULT NULL,
  `order_type_enum` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `is_hidden` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'A timestamp reflecting the time of record-creation.',
  `created_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL COMMENT 'A timestamp reflecting the time of the last change for this record.',
  `updated_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT 'A timestamp reflecting the time of record-deletion.',
  `deleted_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `order_types_client_id_foreign` (`client_id`),
  CONSTRAINT `order_types_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `order_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `order_user` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `order_user_order_id_foreign` (`order_id`),
  KEY `order_user_user_id_foreign` (`user_id`),
  CONSTRAINT `order_user_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_user_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `orders` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `approval_user_id` bigint unsigned DEFAULT NULL,
  `parent_id` bigint unsigned DEFAULT NULL,
  `client_id` bigint unsigned NOT NULL,
  `agent_id` bigint unsigned DEFAULT NULL,
  `contact_id` bigint unsigned DEFAULT NULL,
  `contact_bank_connection_id` bigint unsigned DEFAULT NULL,
  `currency_id` bigint unsigned NOT NULL,
  `address_invoice_id` bigint unsigned NOT NULL,
  `address_delivery_id` bigint unsigned DEFAULT NULL,
  `language_id` bigint unsigned DEFAULT NULL,
  `order_type_id` bigint unsigned NOT NULL,
  `price_list_id` bigint unsigned NOT NULL,
  `unit_price_price_list_id` bigint unsigned DEFAULT NULL,
  `delivery_type_id` bigint unsigned DEFAULT NULL,
  `logistics_id` bigint unsigned DEFAULT NULL,
  `payment_type_id` bigint unsigned NOT NULL,
  `responsible_user_id` bigint unsigned DEFAULT NULL,
  `tax_exemption_id` bigint unsigned DEFAULT NULL,
  `address_invoice` json DEFAULT NULL,
  `address_delivery` json DEFAULT NULL,
  `state` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_state` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `delivery_state` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_target` int unsigned NOT NULL,
  `payment_discount_target` int unsigned DEFAULT NULL,
  `payment_discount_percent` decimal(40,10) DEFAULT NULL,
  `header_discount` decimal(40,10) NOT NULL DEFAULT '0.0000000000',
  `shipping_costs_net_price` decimal(40,10) DEFAULT NULL COMMENT 'A decimal containing the net price of shipping costs.',
  `shipping_costs_gross_price` decimal(40,10) DEFAULT NULL COMMENT 'A decimal containing the gross price of shipping costs.',
  `shipping_costs_vat_price` decimal(40,10) DEFAULT NULL COMMENT 'A decimal containing the vat price of shipping costs.',
  `shipping_costs_vat_rate_percentage` decimal(40,10) DEFAULT NULL COMMENT 'A decimal, containing the vat-rate in percent for the shipping costs, that is cached for easier and faster readability of this order.',
  `total_base_net_price` decimal(40,10) DEFAULT NULL,
  `total_base_gross_price` decimal(40,10) DEFAULT NULL,
  `gross_profit` decimal(40,10) NOT NULL DEFAULT '0.0000000000',
  `total_purchase_price` decimal(40,10) NOT NULL DEFAULT '0.0000000000',
  `total_cost` decimal(40,10) DEFAULT NULL,
  `margin` decimal(40,10) NOT NULL DEFAULT '0.0000000000',
  `total_net_price` decimal(40,10) NOT NULL DEFAULT '0.0000000000',
  `total_gross_price` decimal(40,10) NOT NULL DEFAULT '0.0000000000',
  `total_vats` json DEFAULT NULL,
  `balance` decimal(40,10) DEFAULT NULL,
  `number_of_packages` int unsigned DEFAULT NULL,
  `payment_reminder_days_1` int unsigned NOT NULL,
  `payment_reminder_days_2` int unsigned NOT NULL,
  `payment_reminder_days_3` int unsigned NOT NULL,
  `payment_reminder_current_level` int unsigned DEFAULT NULL,
  `payment_reminder_next_date` date DEFAULT NULL,
  `order_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `commission` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `iban` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `account_holder` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bank_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bic` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `header` longtext COLLATE utf8mb4_unicode_ci,
  `footer` longtext COLLATE utf8mb4_unicode_ci,
  `logistic_note` longtext COLLATE utf8mb4_unicode_ci,
  `tracking_email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_texts` json DEFAULT NULL,
  `order_date` date NOT NULL,
  `invoice_date` date DEFAULT NULL,
  `invoice_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `system_delivery_date` date DEFAULT NULL,
  `system_delivery_date_end` date DEFAULT NULL,
  `customer_delivery_date` date DEFAULT NULL,
  `date_of_approval` date DEFAULT NULL,
  `has_logistic_notify_phone_number` tinyint(1) NOT NULL DEFAULT '0',
  `has_logistic_notify_number` tinyint(1) NOT NULL DEFAULT '0',
  `is_locked` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'If set to true this order cant be edited anymore, this happens usually when the invoice was printed.',
  `is_new_customer` tinyint(1) NOT NULL DEFAULT '0',
  `is_imported` tinyint(1) NOT NULL DEFAULT '0',
  `is_merge_invoice` tinyint(1) NOT NULL DEFAULT '0',
  `is_confirmed` tinyint(1) NOT NULL DEFAULT '0',
  `is_paid` tinyint(1) NOT NULL DEFAULT '0',
  `requires_approval` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'A timestamp reflecting the time of record-creation.',
  `created_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL COMMENT 'A timestamp reflecting the time of the last change for this record.',
  `updated_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT 'A timestamp reflecting the time of record-deletion.',
  `deleted_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `orders_order_number_client_id_unique` (`order_number`,`client_id`),
  KEY `orders_parent_id_foreign` (`parent_id`),
  KEY `orders_client_id_foreign` (`client_id`),
  KEY `orders_language_id_foreign` (`language_id`),
  KEY `orders_order_type_id_foreign` (`order_type_id`),
  KEY `orders_payment_type_id_foreign` (`payment_type_id`),
  KEY `orders_address_invoice_id_foreign` (`address_invoice_id`),
  KEY `orders_address_delivery_id_foreign` (`address_delivery_id`),
  KEY `orders_contact_id_foreign` (`contact_id`),
  KEY `orders_approval_user_id_foreign` (`approval_user_id`),
  KEY `orders_agent_id_foreign` (`agent_id`),
  KEY `orders_responsible_user_id_foreign` (`responsible_user_id`),
  KEY `orders_contact_bank_connection_id_foreign` (`contact_bank_connection_id`),
  KEY `orders_currency_id_foreign` (`currency_id`),
  KEY `orders_price_list_id_foreign` (`price_list_id`),
  CONSTRAINT `orders_address_delivery_id_foreign` FOREIGN KEY (`address_delivery_id`) REFERENCES `addresses` (`id`),
  CONSTRAINT `orders_address_invoice_id_foreign` FOREIGN KEY (`address_invoice_id`) REFERENCES `addresses` (`id`),
  CONSTRAINT `orders_agent_id_foreign` FOREIGN KEY (`agent_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `orders_approval_user_id_foreign` FOREIGN KEY (`approval_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `orders_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`),
  CONSTRAINT `orders_contact_bank_connection_id_foreign` FOREIGN KEY (`contact_bank_connection_id`) REFERENCES `contact_bank_connections` (`id`) ON DELETE SET NULL,
  CONSTRAINT `orders_contact_id_foreign` FOREIGN KEY (`contact_id`) REFERENCES `contacts` (`id`),
  CONSTRAINT `orders_currency_id_foreign` FOREIGN KEY (`currency_id`) REFERENCES `currencies` (`id`),
  CONSTRAINT `orders_language_id_foreign` FOREIGN KEY (`language_id`) REFERENCES `languages` (`id`),
  CONSTRAINT `orders_order_type_id_foreign` FOREIGN KEY (`order_type_id`) REFERENCES `order_types` (`id`),
  CONSTRAINT `orders_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `orders` (`id`),
  CONSTRAINT `orders_payment_type_id_foreign` FOREIGN KEY (`payment_type_id`) REFERENCES `payment_types` (`id`),
  CONSTRAINT `orders_price_list_id_foreign` FOREIGN KEY (`price_list_id`) REFERENCES `price_lists` (`id`),
  CONSTRAINT `orders_responsible_user_id_foreign` FOREIGN KEY (`responsible_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `paid_commissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `paid_commissions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `commission_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `commission` decimal(40,10) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `paid_commissions_commission_id_foreign` (`commission_id`),
  KEY `paid_commissions_user_id_foreign` (`user_id`),
  CONSTRAINT `paid_commissions_commission_id_foreign` FOREIGN KEY (`commission_id`) REFERENCES `commissions` (`id`),
  CONSTRAINT `paid_commissions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `payment_notices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `payment_notices` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `client_id` bigint unsigned NOT NULL,
  `payment_type_id` bigint unsigned NOT NULL,
  `document_type_id` bigint unsigned NOT NULL,
  `payment_notice` json NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'A timestamp reflecting the time of record-creation.',
  `created_by` bigint unsigned DEFAULT NULL COMMENT 'A unique identifier number for the table users of the user that created this record.',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT 'A timestamp reflecting the time of the last change for this record.',
  `updated_by` bigint unsigned DEFAULT NULL COMMENT 'A unique identifier number for the table users of the user that changed this record last.',
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT 'A timestamp reflecting the time of record-deletion.',
  `deleted_by` bigint unsigned DEFAULT NULL COMMENT 'A unique identifier number for the table users of the user that deleted this record.',
  PRIMARY KEY (`id`),
  KEY `payment_notices_client_id_foreign` (`client_id`),
  KEY `payment_notices_payment_type_id_foreign` (`payment_type_id`),
  KEY `payment_notices_document_type_id_foreign` (`document_type_id`),
  KEY `payment_notices_created_by_foreign` (`created_by`),
  KEY `payment_notices_updated_by_foreign` (`updated_by`),
  KEY `payment_notices_deleted_by_foreign` (`deleted_by`),
  CONSTRAINT `payment_notices_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`),
  CONSTRAINT `payment_notices_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  CONSTRAINT `payment_notices_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`),
  CONSTRAINT `payment_notices_document_type_id_foreign` FOREIGN KEY (`document_type_id`) REFERENCES `document_types` (`id`),
  CONSTRAINT `payment_notices_payment_type_id_foreign` FOREIGN KEY (`payment_type_id`) REFERENCES `payment_types` (`id`),
  CONSTRAINT `payment_notices_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `payment_reminder_texts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `payment_reminder_texts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mail_to` json DEFAULT NULL,
  `mail_cc` json DEFAULT NULL,
  `mail_subject` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mail_body` text COLLATE utf8mb4_unicode_ci,
  `reminder_subject` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reminder_body` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `reminder_level` int unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `payment_reminder_texts_uuid_unique` (`uuid`),
  KEY `payment_reminder_texts_reminder_level_index` (`reminder_level`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `payment_reminders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `payment_reminders` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `order_id` bigint unsigned NOT NULL,
  `media_id` bigint unsigned DEFAULT NULL,
  `reminder_level` int unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `deleted_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `payment_reminders_order_id_foreign` (`order_id`),
  KEY `payment_reminders_media_id_foreign` (`media_id`),
  CONSTRAINT `payment_reminders_media_id_foreign` FOREIGN KEY (`media_id`) REFERENCES `media` (`id`) ON DELETE SET NULL,
  CONSTRAINT `payment_reminders_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `payment_runs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `payment_runs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `bank_connection_id` bigint unsigned DEFAULT NULL,
  `state` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'open',
  `payment_run_type_enum` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `instructed_execution_date` date DEFAULT NULL,
  `is_single_booking` tinyint(1) NOT NULL DEFAULT '1',
  `is_instant_payment` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `payment_runs_bank_connection_id_foreign` (`bank_connection_id`),
  CONSTRAINT `payment_runs_bank_connection_id_foreign` FOREIGN KEY (`bank_connection_id`) REFERENCES `bank_connections` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `payment_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `payment_types` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `client_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_reminder_days_1` int DEFAULT NULL,
  `payment_reminder_days_2` int DEFAULT NULL,
  `payment_reminder_days_3` int DEFAULT NULL,
  `payment_target` int DEFAULT NULL,
  `payment_discount_target` int DEFAULT NULL,
  `payment_discount_percentage` decimal(8,2) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `is_direct_debit` tinyint(1) NOT NULL DEFAULT '0',
  `is_default` tinyint(1) NOT NULL DEFAULT '0',
  `is_purchase` tinyint(1) NOT NULL DEFAULT '0',
  `is_sales` tinyint(1) NOT NULL DEFAULT '1',
  `requires_manual_transfer` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'A timestamp reflecting the time of record-creation.',
  `created_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL COMMENT 'A timestamp reflecting the time of the last change for this record.',
  `updated_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT 'A timestamp reflecting the time of record-deletion.',
  `deleted_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `payment_types_client_id_foreign` (`client_id`),
  CONSTRAINT `payment_types_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `permissions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_locked` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`),
  KEY `permissions_name_guard_name_index` (`name`,`guard_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `personal_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `personal_access_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `price_lists`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `price_lists` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT 'An incrementing number to unique identify a record in this table. This also is the primary key of this table.',
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'A 36 character long unique identifier string for a record within the whole application.',
  `parent_id` bigint unsigned DEFAULT NULL,
  `name` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'A string containing a descriptive name for the current price-list.',
  `price_list_code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `rounding_method_enum` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'none',
  `rounding_precision` int DEFAULT NULL,
  `rounding_number` int unsigned DEFAULT NULL,
  `rounding_mode` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_net` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'A boolean deciding if this price-list has prices only for net orders instead of gross orders.',
  `is_default` tinyint(1) NOT NULL DEFAULT '0',
  `is_purchase` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'A timestamp reflecting the time of record-creation.',
  `created_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL COMMENT 'A timestamp reflecting the time of the last change for this record.',
  `updated_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT 'A timestamp reflecting the time of record-deletion.',
  `deleted_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `price_lists_price_list_code_unique` (`price_list_code`),
  KEY `price_lists_parent_id_foreign` (`parent_id`),
  CONSTRAINT `price_lists_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `price_lists` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `prices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `prices` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT 'An incrementing number to uniquely identify a record in this table. This also is the primary key of this table.',
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'A 36 character long unique identifier string for a record within the whole application.',
  `product_id` bigint unsigned NOT NULL COMMENT 'A unique identifier number for the table products.',
  `price_list_id` bigint unsigned NOT NULL COMMENT 'A unique identifier number for the table price_lists.',
  `price` decimal(40,10) NOT NULL COMMENT 'The actual price as number for this database entry.',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'A timestamp reflecting the time of record-creation.',
  `created_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL COMMENT 'A timestamp reflecting the time of the last change for this record.',
  `updated_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT 'A timestamp reflecting the time of record-deletion.',
  `deleted_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `prices_price_list_id_foreign` (`price_list_id`),
  KEY `prices_product_id_foreign` (`product_id`),
  CONSTRAINT `prices_price_list_id_foreign` FOREIGN KEY (`price_list_id`) REFERENCES `price_lists` (`id`),
  CONSTRAINT `prices_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `product_bundle_product`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `product_bundle_product` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint unsigned NOT NULL COMMENT 'Main bundle product containing other products.',
  `bundle_product_id` bigint unsigned NOT NULL COMMENT 'Referenced product of the bundle.',
  `count` decimal(40,10) NOT NULL DEFAULT '1.0000000000',
  PRIMARY KEY (`id`),
  KEY `product_bundle_product_product_id_foreign` (`product_id`),
  KEY `product_bundle_product_bundle_product_id_foreign` (`bundle_product_id`),
  CONSTRAINT `product_bundle_product_bundle_product_id_foreign` FOREIGN KEY (`bundle_product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `product_bundle_product_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `product_cross_selling_product`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `product_cross_selling_product` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `product_cross_selling_id` bigint unsigned NOT NULL,
  `product_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `product_cross_selling_product_unique` (`product_cross_selling_id`,`product_id`),
  KEY `product_cross_selling_product_product_id_foreign` (`product_id`),
  CONSTRAINT `product_cross_selling_product_product_cross_selling_id_foreign` FOREIGN KEY (`product_cross_selling_id`) REFERENCES `product_cross_sellings` (`id`) ON DELETE CASCADE,
  CONSTRAINT `product_cross_selling_product_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `product_cross_sellings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `product_cross_sellings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `product_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `order_column` int NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `product_cross_sellings_product_id_foreign` (`product_id`),
  CONSTRAINT `product_cross_sellings_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `product_option_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `product_option_groups` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'A timestamp reflecting the time of record-creation.',
  `created_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL COMMENT 'A timestamp reflecting the time of the last change for this record.',
  `updated_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT 'A timestamp reflecting the time of record-deletion.',
  `deleted_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `product_options`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `product_options` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `product_option_group_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'A timestamp reflecting the time of record-creation.',
  `created_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL COMMENT 'A timestamp reflecting the time of the last change for this record.',
  `updated_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT 'A timestamp reflecting the time of record-deletion.',
  `deleted_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `product_options_product_option_group_id_foreign` (`product_option_group_id`),
  CONSTRAINT `product_options_product_option_group_id_foreign` FOREIGN KEY (`product_option_group_id`) REFERENCES `product_option_groups` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `product_product_option`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `product_product_option` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint unsigned NOT NULL,
  `product_option_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `product_product_option_product_id_product_option_id_unique` (`product_id`,`product_option_id`),
  KEY `product_product_option_product_option_id_foreign` (`product_option_id`),
  CONSTRAINT `product_product_option_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `product_product_option_product_option_id_foreign` FOREIGN KEY (`product_option_id`) REFERENCES `product_options` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `product_product_property`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `product_product_property` (
  `product_id` bigint unsigned NOT NULL,
  `product_prop_id` bigint unsigned NOT NULL,
  `value` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`product_id`,`product_prop_id`),
  KEY `product_product_property_product_prop_id_foreign` (`product_prop_id`),
  CONSTRAINT `product_product_property_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  CONSTRAINT `product_product_property_product_prop_id_foreign` FOREIGN KEY (`product_prop_id`) REFERENCES `product_properties` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `product_properties`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `product_properties` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `product_property_group_id` bigint unsigned DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `property_type_enum` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'text',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'A timestamp reflecting the time of record-creation.',
  `created_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL COMMENT 'A timestamp reflecting the time of the last change for this record.',
  `updated_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT 'A timestamp reflecting the time of record-deletion.',
  `deleted_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `product_properties_product_property_group_id_foreign` (`product_property_group_id`),
  CONSTRAINT `product_properties_product_property_group_id_foreign` FOREIGN KEY (`product_property_group_id`) REFERENCES `product_property_groups` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `product_property_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `product_property_groups` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `deleted_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `product_supplier`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `product_supplier` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint unsigned NOT NULL,
  `contact_id` bigint unsigned NOT NULL,
  `manufacturer_product_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `purchase_price` decimal(40,10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `product_supplier_product_id_contact_id_unique` (`product_id`,`contact_id`),
  KEY `product_supplier_contact_id_foreign` (`contact_id`),
  CONSTRAINT `product_supplier_contact_id_foreign` FOREIGN KEY (`contact_id`) REFERENCES `contacts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `product_supplier_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `products` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cover_media_id` bigint unsigned DEFAULT NULL,
  `parent_id` bigint unsigned DEFAULT NULL,
  `vat_rate_id` bigint unsigned DEFAULT NULL,
  `unit_id` bigint unsigned DEFAULT NULL,
  `purchase_unit_id` bigint unsigned DEFAULT NULL,
  `reference_unit_id` bigint unsigned DEFAULT NULL,
  `product_number` text COLLATE utf8mb4_unicode_ci,
  `product_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` longtext COLLATE utf8mb4_unicode_ci,
  `weight_gram` decimal(40,10) DEFAULT NULL,
  `dimension_length_mm` decimal(40,10) DEFAULT NULL,
  `dimension_width_mm` decimal(40,10) DEFAULT NULL,
  `dimension_height_mm` decimal(40,10) DEFAULT NULL,
  `selling_unit` decimal(40,10) DEFAULT NULL,
  `basic_unit` decimal(40,10) DEFAULT NULL,
  `time_unit_enum` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ean` text COLLATE utf8mb4_unicode_ci,
  `min_delivery_time` int DEFAULT NULL,
  `max_delivery_time` int DEFAULT NULL,
  `restock_time` int DEFAULT NULL,
  `purchase_steps` double DEFAULT NULL,
  `min_purchase` double DEFAULT NULL,
  `max_purchase` double DEFAULT NULL,
  `seo_keywords` text COLLATE utf8mb4_unicode_ci,
  `posting_account` text COLLATE utf8mb4_unicode_ci,
  `warning_stock_amount` double DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `is_highlight` tinyint(1) NOT NULL DEFAULT '0',
  `is_bundle` tinyint(1) NOT NULL DEFAULT '0',
  `is_service` tinyint(1) NOT NULL DEFAULT '0',
  `is_shipping_free` tinyint(1) NOT NULL DEFAULT '0',
  `is_required_product_serial_number` tinyint(1) NOT NULL DEFAULT '0',
  `is_nos` tinyint(1) NOT NULL DEFAULT '0',
  `is_active_export_to_web_shop` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'A timestamp reflecting the time of record-creation.',
  `created_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL COMMENT 'A timestamp reflecting the time of the last change for this record.',
  `updated_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT 'A timestamp reflecting the time of record-deletion.',
  `deleted_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `products_parent_id_foreign` (`parent_id`),
  KEY `products_vat_rate_id_foreign` (`vat_rate_id`),
  KEY `products_unit_id_foreign` (`unit_id`),
  KEY `products_purchase_unit_id_foreign` (`purchase_unit_id`),
  KEY `products_reference_unit_id_foreign` (`reference_unit_id`),
  KEY `products_cover_media_id_foreign` (`cover_media_id`),
  CONSTRAINT `products_cover_media_id_foreign` FOREIGN KEY (`cover_media_id`) REFERENCES `media` (`id`) ON DELETE SET NULL,
  CONSTRAINT `products_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `products` (`id`),
  CONSTRAINT `products_purchase_unit_id_foreign` FOREIGN KEY (`purchase_unit_id`) REFERENCES `units` (`id`),
  CONSTRAINT `products_reference_unit_id_foreign` FOREIGN KEY (`reference_unit_id`) REFERENCES `units` (`id`),
  CONSTRAINT `products_unit_id_foreign` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`),
  CONSTRAINT `products_vat_rate_id_foreign` FOREIGN KEY (`vat_rate_id`) REFERENCES `vat_rates` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `projects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `projects` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `client_id` bigint unsigned NOT NULL,
  `contact_id` bigint unsigned DEFAULT NULL,
  `order_id` bigint unsigned DEFAULT NULL,
  `responsible_user_id` bigint unsigned DEFAULT NULL,
  `parent_id` bigint unsigned DEFAULT NULL,
  `project_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `state` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'open',
  `progress` decimal(11,10) NOT NULL DEFAULT '0.0000000000',
  `time_budget` bigint unsigned DEFAULT NULL COMMENT 'Time budget in minutes.',
  `budget` decimal(40,10) DEFAULT NULL,
  `total_cost` decimal(40,10) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'A timestamp reflecting the time of record-creation.',
  `created_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL COMMENT 'A timestamp reflecting the time of the last change for this record.',
  `updated_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT 'A timestamp reflecting the time of record-deletion.',
  `deleted_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `projects_parent_id_foreign` (`parent_id`),
  KEY `projects_contact_id_foreign` (`contact_id`),
  KEY `projects_order_id_foreign` (`order_id`),
  KEY `projects_responsible_user_id_foreign` (`responsible_user_id`),
  KEY `projects_client_id_foreign` (`client_id`),
  CONSTRAINT `projects_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE,
  CONSTRAINT `projects_contact_id_foreign` FOREIGN KEY (`contact_id`) REFERENCES `contacts` (`id`) ON DELETE SET NULL,
  CONSTRAINT `projects_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL,
  CONSTRAINT `projects_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `projects` (`id`),
  CONSTRAINT `projects_responsible_user_id_foreign` FOREIGN KEY (`responsible_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `purchase_invoice_positions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `purchase_invoice_positions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `purchase_invoice_id` bigint unsigned NOT NULL,
  `ledger_account_id` bigint unsigned DEFAULT NULL,
  `product_id` bigint unsigned DEFAULT NULL,
  `vat_rate_id` bigint unsigned DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `amount` decimal(40,10) NOT NULL DEFAULT '1.0000000000',
  `unit_price` decimal(40,10) DEFAULT NULL,
  `total_price` decimal(40,10) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `purchase_invoice_positions_purchase_invoice_id_foreign` (`purchase_invoice_id`),
  KEY `purchase_invoice_positions_ledger_account_id_foreign` (`ledger_account_id`),
  KEY `purchase_invoice_positions_product_id_foreign` (`product_id`),
  KEY `purchase_invoice_positions_vat_rate_id_foreign` (`vat_rate_id`),
  CONSTRAINT `purchase_invoice_positions_ledger_account_id_foreign` FOREIGN KEY (`ledger_account_id`) REFERENCES `ledger_accounts` (`id`) ON DELETE SET NULL,
  CONSTRAINT `purchase_invoice_positions_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL,
  CONSTRAINT `purchase_invoice_positions_purchase_invoice_id_foreign` FOREIGN KEY (`purchase_invoice_id`) REFERENCES `purchase_invoices` (`id`) ON DELETE CASCADE,
  CONSTRAINT `purchase_invoice_positions_vat_rate_id_foreign` FOREIGN KEY (`vat_rate_id`) REFERENCES `vat_rates` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `purchase_invoices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `purchase_invoices` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `client_id` bigint unsigned DEFAULT NULL,
  `contact_id` bigint unsigned DEFAULT NULL,
  `currency_id` bigint unsigned DEFAULT NULL,
  `lay_out_user_id` bigint unsigned DEFAULT NULL,
  `media_id` bigint unsigned DEFAULT NULL,
  `order_id` bigint unsigned DEFAULT NULL,
  `order_type_id` bigint unsigned DEFAULT NULL,
  `payment_type_id` bigint unsigned DEFAULT NULL,
  `invoice_date` date NOT NULL,
  `system_delivery_date` date DEFAULT NULL,
  `system_delivery_date_end` date DEFAULT NULL,
  `invoice_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `iban` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `account_holder` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bank_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bic` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_net` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `deleted_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `purchase_invoices_hash_unique` (`hash`),
  KEY `purchase_invoices_client_id_foreign` (`client_id`),
  KEY `purchase_invoices_contact_id_foreign` (`contact_id`),
  KEY `purchase_invoices_currency_id_foreign` (`currency_id`),
  KEY `purchase_invoices_media_id_foreign` (`media_id`),
  KEY `purchase_invoices_order_id_foreign` (`order_id`),
  KEY `purchase_invoices_order_type_id_foreign` (`order_type_id`),
  KEY `purchase_invoices_payment_type_id_foreign` (`payment_type_id`),
  KEY `purchase_invoices_lay_out_user_id_foreign` (`lay_out_user_id`),
  CONSTRAINT `purchase_invoices_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE SET NULL,
  CONSTRAINT `purchase_invoices_contact_id_foreign` FOREIGN KEY (`contact_id`) REFERENCES `contacts` (`id`) ON DELETE SET NULL,
  CONSTRAINT `purchase_invoices_currency_id_foreign` FOREIGN KEY (`currency_id`) REFERENCES `currencies` (`id`) ON DELETE SET NULL,
  CONSTRAINT `purchase_invoices_lay_out_user_id_foreign` FOREIGN KEY (`lay_out_user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `purchase_invoices_media_id_foreign` FOREIGN KEY (`media_id`) REFERENCES `media` (`id`) ON DELETE SET NULL,
  CONSTRAINT `purchase_invoices_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL,
  CONSTRAINT `purchase_invoices_order_type_id_foreign` FOREIGN KEY (`order_type_id`) REFERENCES `order_types` (`id`) ON DELETE SET NULL,
  CONSTRAINT `purchase_invoices_payment_type_id_foreign` FOREIGN KEY (`payment_type_id`) REFERENCES `payment_types` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `push_subscriptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `push_subscriptions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `subscribable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subscribable_id` bigint unsigned NOT NULL,
  `endpoint` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `public_key` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `auth_token` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `content_encoding` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `push_subscriptions_endpoint_unique` (`endpoint`),
  KEY `push_subscriptions_subscribable_type_subscribable_id_index` (`subscribable_type`,`subscribable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `queue_monitorables`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `queue_monitorables` (
  `queue_monitor_id` bigint unsigned NOT NULL,
  `queue_monitorable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue_monitorable_id` bigint unsigned NOT NULL,
  `notify_on_finish` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`queue_monitor_id`,`queue_monitorable_id`,`queue_monitorable_type`),
  KEY `queue_monitorable_index` (`queue_monitorable_type`,`queue_monitorable_id`),
  CONSTRAINT `queue_monitorables_queue_monitor_id_foreign` FOREIGN KEY (`queue_monitor_id`) REFERENCES `queue_monitors` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `queue_monitors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `queue_monitors` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `job_uuid` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `job_batch_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `job_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `state` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'running',
  `queued_at` datetime DEFAULT NULL,
  `started_at` timestamp NULL DEFAULT NULL,
  `started_at_exact` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `finished_at` timestamp NULL DEFAULT NULL,
  `finished_at_exact` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `attempt` int unsigned NOT NULL DEFAULT '0',
  `retried` tinyint(1) NOT NULL DEFAULT '0',
  `progress` decimal(11,10) NOT NULL DEFAULT '0.0000000000',
  `exception` text COLLATE utf8mb4_unicode_ci,
  `exception_message` text COLLATE utf8mb4_unicode_ci,
  `exception_class` text COLLATE utf8mb4_unicode_ci,
  `data` json DEFAULT NULL,
  `accept` text COLLATE utf8mb4_unicode_ci,
  `reject` text COLLATE utf8mb4_unicode_ci,
  `message` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `queue_monitors_job_batch_id_index` (`job_batch_id`),
  KEY `queue_monitors_job_id_index` (`job_id`),
  KEY `queue_monitors_started_at_index` (`started_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `role_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `role_has_permissions` (
  `permission_id` bigint unsigned NOT NULL,
  `role_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`role_id`),
  KEY `role_has_permissions_role_id_foreign` (`role_id`),
  CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `role_ticket_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `role_ticket_type` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `role_id` bigint unsigned NOT NULL,
  `ticket_type_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `role_ticket_type_role_id_foreign` (`role_id`),
  KEY `role_ticket_type_ticket_type_id_foreign` (`ticket_type_id`),
  CONSTRAINT `role_ticket_type_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_ticket_type_ticket_type_id_foreign` FOREIGN KEY (`ticket_type_id`) REFERENCES `ticket_types` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `schedules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `schedules` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `class` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `cron` json NOT NULL,
  `parameters` json DEFAULT NULL,
  `cron_expression` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `due_at` datetime DEFAULT NULL,
  `ends_at` datetime DEFAULT NULL,
  `recurrences` int unsigned DEFAULT NULL,
  `current_recurrence` int unsigned DEFAULT NULL,
  `last_success` datetime DEFAULT NULL,
  `last_run` datetime DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `deleted_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sepa_mandates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sepa_mandates` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `client_id` bigint unsigned NOT NULL,
  `contact_id` bigint unsigned NOT NULL,
  `contact_bank_connection_id` bigint unsigned NOT NULL,
  `mandate_reference_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `signed_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'A timestamp reflecting the time of record-creation.',
  `created_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL COMMENT 'A timestamp reflecting the time of the last change for this record.',
  `updated_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT 'A timestamp reflecting the time of record-deletion.',
  `deleted_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sepa_mandates_client_id_mandate_reference_number_unique` (`client_id`,`mandate_reference_number`),
  KEY `sepa_mandates_contact_id_foreign` (`contact_id`),
  KEY `sepa_mandates_bank_connection_id_foreign` (`contact_bank_connection_id`),
  CONSTRAINT `sepa_mandates_bank_connection_id_foreign` FOREIGN KEY (`contact_bank_connection_id`) REFERENCES `contact_bank_connections` (`id`),
  CONSTRAINT `sepa_mandates_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`),
  CONSTRAINT `sepa_mandates_contact_id_foreign` FOREIGN KEY (`contact_id`) REFERENCES `contacts` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `serial_number_ranges`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `serial_number_ranges` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `unique_key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `client_id` bigint unsigned DEFAULT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned DEFAULT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `current_number` bigint unsigned NOT NULL DEFAULT '0',
  `prefix` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `suffix` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `length` int DEFAULT NULL COMMENT 'The length of the serial number. The Serialnumber will be padded with leading zeros.',
  `is_pre_filled` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'A flag to indicate if the serialnumber is picked from the serial_numbers table.',
  `is_randomized` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'A flag indicating whether this range generates a random serial number.',
  `stores_serial_numbers` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'A flag indicating whether this range creates a new serial_numbers record.',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'A timestamp reflecting the time of record-creation.',
  `created_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL COMMENT 'A timestamp reflecting the time of the last change for this record.',
  `updated_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT 'A timestamp reflecting the time of record-deletion.',
  `deleted_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `serial_number_ranges_unique_key_unique` (`unique_key`),
  KEY `serial_number_ranges_client_id_foreign` (`client_id`),
  CONSTRAINT `serial_number_ranges_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `serial_numbers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `serial_numbers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `serial_number_range_id` bigint unsigned DEFAULT NULL,
  `product_id` bigint unsigned DEFAULT NULL,
  `address_id` bigint unsigned DEFAULT NULL,
  `order_position_id` bigint unsigned DEFAULT NULL,
  `serial_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'A timestamp reflecting the time of record-creation.',
  `created_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL COMMENT 'A timestamp reflecting the time of the last change for this record.',
  `updated_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `serial_numbers_serial_number_range_id_foreign` (`serial_number_range_id`),
  KEY `serial_numbers_product_id_foreign` (`product_id`),
  KEY `serial_numbers_address_id_foreign` (`address_id`),
  KEY `serial_numbers_order_position_id_foreign` (`order_position_id`),
  CONSTRAINT `serial_numbers_address_id_foreign` FOREIGN KEY (`address_id`) REFERENCES `addresses` (`id`),
  CONSTRAINT `serial_numbers_order_position_id_foreign` FOREIGN KEY (`order_position_id`) REFERENCES `order_positions` (`id`),
  CONSTRAINT `serial_numbers_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  CONSTRAINT `serial_numbers_serial_number_range_id_foreign` FOREIGN KEY (`serial_number_range_id`) REFERENCES `serial_number_ranges` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `model_id` bigint unsigned DEFAULT NULL,
  `settings` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `settings_model_id_model_type_key_unique` (`model_id`,`model_type`,`key`),
  KEY `settings_model_type_model_id_index` (`model_type`,`model_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `shopware_ids`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `shopware_ids` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `auto_increment` bigint unsigned DEFAULT NULL,
  `shopware_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `parent_id` bigint unsigned DEFAULT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `entity` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `shopware_ids_model_type_model_id_dto_unique` (`model_type`,`model_id`,`entity`),
  KEY `shopware_ids_model_type_model_id_index` (`model_type`,`model_id`),
  KEY `shopware_ids_parent_id_foreign` (`parent_id`),
  KEY `shopware_ids_dto_index` (`entity`),
  CONSTRAINT `shopware_ids_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `shopware_ids` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `snapshots`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `snapshots` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  `snapshot` json NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `snapshots_model_type_model_id_index` (`model_type`,`model_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `stock_postings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `stock_postings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `warehouse_id` bigint unsigned NOT NULL,
  `product_id` bigint unsigned NOT NULL,
  `stock` decimal(40,10) DEFAULT NULL,
  `posting` decimal(40,10) NOT NULL,
  `purchase_price` decimal(40,10) DEFAULT NULL COMMENT 'The full price paid for the entirety of this stock posting.',
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'A timestamp reflecting the time of record-creation.',
  `created_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL COMMENT 'A timestamp reflecting the time of the last change for this record.',
  `updated_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT 'A timestamp reflecting the time of record-deletion.',
  `deleted_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `stock_postings_warehouse_id_foreign` (`warehouse_id`),
  KEY `stock_postings_product_id_foreign` (`product_id`),
  CONSTRAINT `stock_postings_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  CONSTRAINT `stock_postings_warehouse_id_foreign` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `taggables`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `taggables` (
  `tag_id` bigint unsigned NOT NULL,
  `taggable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `taggable_id` bigint unsigned NOT NULL,
  UNIQUE KEY `taggables_tag_id_taggable_id_taggable_type_unique` (`tag_id`,`taggable_id`,`taggable_type`),
  KEY `taggables_taggable_type_taggable_id_index` (`taggable_type`,`taggable_id`),
  CONSTRAINT `taggables_tag_id_foreign` FOREIGN KEY (`tag_id`) REFERENCES `tags` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `tags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tags` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` json NOT NULL,
  `slug` json NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `color` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `order_column` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `task_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `task_user` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `task_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `task_user_task_id_foreign` (`task_id`),
  KEY `task_user_user_id_foreign` (`user_id`),
  CONSTRAINT `task_user_task_id_foreign` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE,
  CONSTRAINT `task_user_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `tasks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tasks` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `project_id` bigint unsigned DEFAULT NULL,
  `responsible_user_id` bigint unsigned DEFAULT NULL,
  `order_position_id` bigint unsigned DEFAULT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `model_id` bigint unsigned DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `start_date` date DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `priority` int unsigned NOT NULL DEFAULT '0',
  `state` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'open',
  `progress` decimal(11,10) NOT NULL DEFAULT '0.0000000000',
  `time_budget` bigint unsigned DEFAULT NULL COMMENT 'Time budget in minutes.',
  `budget` decimal(40,10) DEFAULT NULL,
  `total_cost` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'A timestamp reflecting the time of record-creation.',
  `created_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL COMMENT 'A timestamp reflecting the time of the last change for this record.',
  `updated_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT 'A timestamp reflecting the time of record-deletion.',
  `deleted_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tasks_project_id_foreign` (`project_id`),
  KEY `tasks_responsible_user_id_foreign` (`responsible_user_id`),
  KEY `tasks_order_position_id_foreign` (`order_position_id`),
  KEY `tasks_model_type_model_id_index` (`model_type`,`model_id`),
  CONSTRAINT `tasks_order_position_id_foreign` FOREIGN KEY (`order_position_id`) REFERENCES `order_positions` (`id`) ON DELETE SET NULL,
  CONSTRAINT `tasks_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE SET NULL,
  CONSTRAINT `tasks_responsible_user_id_foreign` FOREIGN KEY (`responsible_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ticket_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ticket_types` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `deleted_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ticket_types_model_type_index` (`model_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ticket_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ticket_user` (
  `ticket_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`ticket_id`,`user_id`),
  KEY `ticket_user_user_id_foreign` (`user_id`),
  CONSTRAINT `ticket_user_ticket_id_foreign` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`),
  CONSTRAINT `ticket_user_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `tickets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tickets` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `authenticatable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `authenticatable_id` bigint unsigned NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `model_id` bigint unsigned DEFAULT NULL,
  `ticket_type_id` bigint unsigned DEFAULT NULL,
  `ticket_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `state` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `total_cost` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'A timestamp reflecting the time of record-creation.',
  `created_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL COMMENT 'A timestamp reflecting the time of the last change for this record.',
  `updated_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT 'A timestamp reflecting the time of record-deletion.',
  `deleted_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tickets_authenticatable_type_authenticatable_id_index` (`authenticatable_type`,`authenticatable_id`),
  KEY `tickets_model_type_model_id_index` (`model_type`,`model_id`),
  KEY `tickets_ticket_type_id_foreign` (`ticket_type_id`),
  CONSTRAINT `tickets_ticket_type_id_foreign` FOREIGN KEY (`ticket_type_id`) REFERENCES `ticket_types` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `max_uses` int unsigned DEFAULT NULL,
  `uses` int unsigned DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `transactions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `bank_connection_id` bigint unsigned NOT NULL,
  `currency_id` bigint unsigned NOT NULL,
  `parent_id` bigint unsigned DEFAULT NULL,
  `order_id` bigint unsigned DEFAULT NULL,
  `value_date` date NOT NULL,
  `booking_date` date NOT NULL,
  `amount` decimal(40,10) NOT NULL,
  `purpose` varchar(2000) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `counterpart_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `counterpart_account_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `counterpart_iban` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `counterpart_bic` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `counterpart_bank_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `deleted_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `transactions_account_id_foreign` (`bank_connection_id`),
  KEY `transactions_currency_id_foreign` (`currency_id`),
  KEY `transactions_parent_id_foreign` (`parent_id`),
  KEY `transactions_order_id_foreign` (`order_id`),
  CONSTRAINT `transactions_account_id_foreign` FOREIGN KEY (`bank_connection_id`) REFERENCES `bank_connections` (`id`),
  CONSTRAINT `transactions_currency_id_foreign` FOREIGN KEY (`currency_id`) REFERENCES `currencies` (`id`),
  CONSTRAINT `transactions_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
  CONSTRAINT `transactions_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `transactions` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `units`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `units` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `abbreviation` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'A timestamp reflecting the time of record-creation.',
  `created_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL COMMENT 'A timestamp reflecting the time of the last change for this record.',
  `updated_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT 'A timestamp reflecting the time of record-deletion.',
  `deleted_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone_3cx_extension` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `firstname` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `lastname` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `iban` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `account_holder` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bank_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bic` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cost_per_hour` decimal(10,2) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `is_dark_mode` tinyint(1) NOT NULL DEFAULT '0',
  `two_factor_secret` text COLLATE utf8mb4_unicode_ci,
  `two_factor_recovery_codes` text COLLATE utf8mb4_unicode_ci,
  `two_factor_confirmed_at` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `language_id` bigint unsigned NOT NULL,
  `parent_id` bigint unsigned DEFAULT NULL,
  `currency_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'A timestamp reflecting the time of record-creation.',
  `created_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL COMMENT 'A timestamp reflecting the time of the last change for this record.',
  `updated_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT 'A timestamp reflecting the time of record-deletion.',
  `deleted_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  UNIQUE KEY `users_uuid_unique` (`uuid`),
  UNIQUE KEY `users_user_code_unique` (`user_code`),
  KEY `users_language_id_foreign` (`language_id`),
  KEY `users_currency_id_foreign` (`currency_id`),
  KEY `users_parent_id_foreign` (`parent_id`),
  CONSTRAINT `users_currency_id_foreign` FOREIGN KEY (`currency_id`) REFERENCES `currencies` (`id`),
  CONSTRAINT `users_language_id_foreign` FOREIGN KEY (`language_id`) REFERENCES `languages` (`id`),
  CONSTRAINT `users_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `vat_rates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `vat_rates` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `rate_percentage` decimal(40,10) NOT NULL,
  `footer_text` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'A timestamp reflecting the time of record-creation.',
  `created_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL COMMENT 'A timestamp reflecting the time of the last change for this record.',
  `updated_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT 'A timestamp reflecting the time of record-deletion.',
  `deleted_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `warehouses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `warehouses` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `address_id` bigint unsigned DEFAULT NULL,
  `name` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_default` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'A timestamp reflecting the time of record-creation.',
  `created_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL COMMENT 'A timestamp reflecting the time of the last change for this record.',
  `updated_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT 'A timestamp reflecting the time of record-deletion.',
  `deleted_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `warehouses_address_id_foreign` (`address_id`),
  CONSTRAINT `warehouses_address_id_foreign` FOREIGN KEY (`address_id`) REFERENCES `addresses` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `widgets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `widgets` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `widgetable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `widgetable_id` bigint unsigned NOT NULL,
  `component_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `config` json DEFAULT NULL,
  `height` int unsigned NOT NULL DEFAULT '1',
  `width` int unsigned NOT NULL DEFAULT '1',
  `order_column` int unsigned NOT NULL DEFAULT '0',
  `order_row` int unsigned NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `widgets_widgetable_type_widgetable_id_index` (`widgetable_type`,`widgetable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `work_time_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `work_time_types` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_billable` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `deleted_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `work_times`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `work_times` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `contact_id` bigint unsigned DEFAULT NULL,
  `order_position_id` bigint unsigned DEFAULT NULL,
  `parent_id` bigint unsigned DEFAULT NULL,
  `work_time_type_id` bigint unsigned DEFAULT NULL,
  `trackable_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `trackable_id` bigint unsigned DEFAULT NULL,
  `started_at` datetime NOT NULL,
  `ended_at` datetime DEFAULT NULL,
  `paused_time_ms` bigint unsigned NOT NULL DEFAULT '0',
  `total_time_ms` bigint unsigned NOT NULL DEFAULT '0',
  `total_cost` decimal(10,2) DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `is_billable` tinyint(1) NOT NULL DEFAULT '0',
  `is_daily_work_time` tinyint(1) NOT NULL DEFAULT '0',
  `is_locked` tinyint(1) NOT NULL DEFAULT '0',
  `is_pause` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `deleted_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `work_times_order_position_id_unique` (`order_position_id`),
  KEY `work_times_trackable_type_trackable_id_index` (`trackable_type`,`trackable_id`),
  KEY `work_times_user_id_foreign` (`user_id`),
  KEY `work_times_work_time_type_id_foreign` (`work_time_type_id`),
  KEY `work_times_contact_id_foreign` (`contact_id`),
  KEY `work_times_parent_id_foreign` (`parent_id`),
  CONSTRAINT `work_times_contact_id_foreign` FOREIGN KEY (`contact_id`) REFERENCES `contacts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `work_times_order_position_id_foreign` FOREIGN KEY (`order_position_id`) REFERENCES `order_positions` (`id`) ON DELETE SET NULL,
  CONSTRAINT `work_times_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `work_times` (`id`) ON DELETE CASCADE,
  CONSTRAINT `work_times_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `work_times_work_time_type_id_foreign` FOREIGN KEY (`work_time_type_id`) REFERENCES `work_time_types` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (1,'0001_01_01_000000_create_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (2,'0001_01_01_000001_create_cache_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (3,'0001_01_01_000002_create_jobs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (4,'2014_10_12_000000_create_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (5,'2014_10_12_100000_create_password_reset_tokens_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (6,'2014_10_12_200000_add_two_factor_columns_to_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (7,'2019_08_19_000000_create_failed_jobs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (8,'2019_12_14_000001_create_personal_access_tokens_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (9,'2021_07_15_122950_create_settings_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (10,'2021_07_26_094511_create_jobs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (11,'2021_07_26_113227_create_project_categories_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (12,'2021_07_26_130827_create_additional_columns_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (13,'2021_08_04_114403_create_structures_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (14,'2021_08_05_130242_create_project_category_templates_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (15,'2021_08_05_131337_create_projects_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (16,'2021_08_05_131338_create_project_tasks_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (17,'2021_08_05_131339_create_project_category_template_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (18,'2021_08_16_130750_create_model_has_values_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (19,'2021_08_19_114927_create_logs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (20,'2021_08_20_153643_create_model_has_external_record',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (21,'2021_09_02_122630_create_media_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (22,'2021_09_29_091911_create_comments_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (23,'2021_10_06_113937_change_project_name_and_display_name_to_text_on_projects_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (24,'2021_10_10_122945_add_created_by_and_updated_by_to_project_categories_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (25,'2021_10_10_130317_add_created_by_and_updated_by_to_project_category_templates_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (26,'2021_10_12_000000_create_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (27,'2021_10_13_120546_add_user_code_to_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (28,'2021_10_13_144444_create_contacts_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (29,'2021_10_13_144457_create_addresses_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (30,'2021_10_13_153217_refactor_customer_id_to_address_id_on_project_tasks_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (31,'2021_10_15_123222_create_order_positions_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (32,'2021_10_15_125009_add_order_position_id_to_project_tasks_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (33,'2021_10_18_171234_make_uuid_unique_on_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (34,'2021_10_19_142100_create_clients_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (35,'2021_10_27_124958_add_comment_id_on_comments_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (36,'2021_10_27_125451_add_missing_foreign_keys_on_project_categories_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (37,'2021_10_27_125509_add_missing_foreign_keys_on_project_category_templates_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (38,'2021_10_27_125742_add_index_to_model_on_additional_columns_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (39,'2021_10_27_150710_refactor_project_categories_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (40,'2021_10_27_152910_create_category_project_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (41,'2021_10_27_161308_remove_notes_from_project_tasks_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (42,'2021_11_02_133213_create_client_user_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (43,'2021_11_02_154232_create_currencies_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (44,'2021_11_02_154642_create_languages_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (45,'2021_11_02_162940_create_countries_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (46,'2021_11_02_162948_create_country_regions_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (47,'2021_11_02_165053_update_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (48,'2021_11_04_132124_add_country_id_on_clients_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (49,'2021_11_10_134101_create_event_notifications_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (50,'2021_11_12_094418_add_fields_to_addresses_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (51,'2021_11_17_121820_refactor_event_notifications_table_and_rename_table_to_event_subscriptions',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (52,'2021_12_02_105616_add_fields_to_contacts_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (53,'2021_12_02_114732_create_units_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (54,'2021_12_02_115214_create_product_option_groups_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (55,'2021_12_02_120025_create_product_options_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (56,'2021_12_02_122611_create_warehouses_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (57,'2021_12_02_122613_create_product_properties_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (58,'2021_12_02_122614_create_vat_rates_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (59,'2021_12_02_122615_create_products_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (60,'2021_12_02_124110_create_address_product_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (61,'2021_12_02_124111_create_product_product_option_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (62,'2021_12_02_124113_create_category_product_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (63,'2021_12_02_124114_create_stock_postings_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (64,'2021_12_02_152625_add_iso_on_languages_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (65,'2021_12_02_155238_add_fields_to_addresses',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (66,'2021_12_02_173420_add_unique_iso_on_currencies_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (67,'2021_12_02_180218_remove_nullables_on_countries_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (68,'2021_12_02_182907_create_contact_bank_connections_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (69,'2021_12_02_184018_create_sepa_mandates_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (70,'2021_12_03_104415_refactor_clients_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (71,'2021_12_03_122108_add_unique_user_code_on_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (72,'2021_12_03_122659_create_payment_types_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (73,'2021_12_03_123913_create_document_types_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (74,'2021_12_03_125031_create_payment_notices_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (75,'2021_12_03_131433_create_snapshots_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (76,'2021_12_03_133346_create_order_types_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (77,'2021_12_03_133801_create_orders_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (78,'2021_12_03_135435_create_document_generation_settings_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (79,'2021_12_03_155121_create_product_product_properties_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (80,'2021_12_07_124605_create_record_histories_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (81,'2021_12_09_175411_refactor_countries_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (82,'2021_12_13_130126_change_name_to_json_on_categories_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (83,'2021_12_13_130127_change_value_to_json_on_model_has_values_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (84,'2021_12_13_130128_change_name_to_json_on_project_category_templates_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (85,'2021_12_13_130129_change_name_to_json_on_project_tasks_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (86,'2021_12_13_130130_change_name_to_json_on_countries_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (87,'2021_12_13_130620_change_name_to_json_on_country_regions_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (88,'2021_12_13_130716_change_translatable_columns_to_json_on_document_types_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (89,'2021_12_13_130748_change_name_to_json_on_languages_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (90,'2021_12_13_130813_change_translatable_columns_to_json_on_orders_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (91,'2021_12_13_130908_change_name_and_description_to_json_on_order_types_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (92,'2021_12_13_130958_change_payment_notice_to_json_on_payment_notices_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (93,'2021_12_13_131019_change_name_and_description_to_json_on_payment_types_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (94,'2021_12_22_131825_create_interface_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (95,'2022_01_14_124331_add_payment_type_id_foreign_key_on_contacts_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (96,'2022_01_14_124616_add_client_id_foreign_key_on_contacts_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (97,'2022_01_17_164444_change_latitude_and_longitude_columns_on_addresses_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (98,'2022_01_19_141649_rename_booleans_on_contacts_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (99,'2022_02_03_211648_create_print_data_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (100,'2022_02_05_100813_create_presentations_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (101,'2022_02_10_095414_remove_request_hash_column_from_print_data',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (102,'2022_02_16_111216_add_commoncolumns_to_print_data_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (103,'2022_02_16_125326_rename_category_id_to_parent_id_on_categories_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (104,'2022_02_16_125402_rename_comment_id_to_parent_id_on_comments_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (105,'2022_02_16_125432_rename_project_id_to_parent_id_on_projects_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (106,'2022_02_22_123829_drop_structures_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (107,'2022_02_24_221803_add_sort_on_print_data_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (108,'2022_03_22_120202_create_ticket_statuses_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (109,'2022_03_22_130657_create_tickets_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (110,'2022_03_22_135412_create_ticket_user_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (111,'2022_03_24_115836_create_serial_number_ranges_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (112,'2022_03_24_115934_create_serial_numbers_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (113,'2022_03_25_135832_add_login_credentials_on_addresses_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (114,'2022_03_28_104423_create_permission_tables',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (115,'2022_03_28_150203_add_is_locked_to_permissions_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (116,'2022_04_04_142444_add_category_id_to_media_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (117,'2022_04_04_160347_add_model_to_categories_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (118,'2022_04_04_160418_drop_is_project_category_from_categories_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (119,'2022_04_20_213346_rename_model_to_model_type_on_categories',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (120,'2022_04_21_114304_add_defaul_value_for_is_main_address_on_addresses',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (121,'2022_04_21_145936_rename_model_to_model_type_on_additional_columns',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (122,'2022_04_21_152208_create_categorizables_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (123,'2022_04_21_154749_drop_category_id_on_project_tasks',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (124,'2022_04_21_154950_drop_category_id_on_media',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (125,'2022_04_25_203019_drop_category_project_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (126,'2022_04_26_164214_drop_category_product_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (127,'2022_05_04_150548_drop_project_category_template_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (128,'2022_05_09_103353_add_is_sticky_to_comments_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (129,'2022_05_11_090844_add_fields_to_additional_columns_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (130,'2022_05_11_160548_add_purchase_price_to_stock_postings_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (131,'2022_05_25_155629_create_discounts_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (132,'2022_05_25_163754_create_price_lists_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (133,'2022_05_25_164401_create_prices_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (134,'2022_05_25_165029_add_fields_and_relations_to_order_positions_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (135,'2022_05_30_094008_create_address_types_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (136,'2022_05_30_094353_create_address_address_type_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (137,'2022_05_30_102802_create_address_address_type_order_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (138,'2022_05_30_104002_add_client_id_to_addresses_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (139,'2022_05_31_111952_create_locks_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (140,'2022_06_14_113518_create_email_templates_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (141,'2022_06_14_113533_create_emails_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (142,'2022_08_05_033747_update_price_list_id_foreign_key_on_order_positions_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (143,'2022_08_08_143407_change_name_to_unique_on_table_interface_users',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (144,'2022_08_10_112125_rename_affix_to_suffix_on_serial_number_ranges',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (145,'2022_08_19_085319_drop_is_locked_on_orders_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (146,'2022_08_21_200551_add_missing_fields_to_orders_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (147,'2022_08_21_211321_add_columns_to_price_lists_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (148,'2022_08_21_221720_change_columns_on_products_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (149,'2022_08_22_104642_cleanup_order_positions_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (150,'2022_08_22_162900_make_address_id_nullable_on_warehouses_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (151,'2022_08_25_140352_create_contact_options_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (152,'2022_08_26_111946_add_parent_id_to_media_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (153,'2022_08_29_165548_add_morph_to_settings_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (154,'2022_08_30_090725_add_is_customer_editable_to_additional_columns_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (155,'2022_09_01_131105_create_language_lines_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (156,'2022_09_01_175011_drop_ticket_status_id_on_tickets_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (157,'2022_09_01_175344_drop_ticket_statuses_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (158,'2022_09_08_090944_add_address_ids_to_orders_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (159,'2022_09_09_124934_add_currency_id_to_orders_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (160,'2022_09_09_134725_add_columns_to_order_positions_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (161,'2022_09_09_201241_rename_vat_rate_on_vat_rates_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (162,'2022_09_10_105750_create_tag_tables',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (163,'2022_09_19_091542_change_unit_price_list_id_to_nullable_on_orders_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (164,'2022_09_19_122359_create_calendars_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (165,'2022_09_19_122459_create_calendar_events_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (166,'2022_09_19_122500_create_calendar_groups_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (167,'2022_09_19_122500_create_calendarables_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (168,'2022_09_19_122500_create_inviteables_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (169,'2022_09_26_090745_add_state_to_tickets_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (170,'2022_09_26_093237_create_state_settings_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (171,'2022_09_26_115934_add_states_to_orders_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (172,'2022_09_27_092115_create_notifications_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (173,'2022_09_30_144542_create_ticket_types_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (174,'2022_09_30_151137_add_model_morph_to_tickets_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (175,'2022_09_30_164419_add_model_id_to_additional_columns_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (176,'2022_10_07_150608_create_tokens_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (177,'2022_10_11_152120_update_unique_constraint_on_additional_columns_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (178,'2022_10_11_155202_add_model_type_to_ticket_types_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (179,'2022_10_14_122239_create_notification_settings_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (180,'2022_10_17_092931_add_index_on_permissions_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (181,'2022_10_19_083928_add_is_primary_to_contact_options',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (182,'2022_10_19_152650_add_morphs_to_serial_number_ranges_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (183,'2022_10_21_110407_create_activity_log_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (184,'2022_10_25_080839_add_addresses_to_orders_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (185,'2022_10_25_084355_add_address_to_address_address_type_order_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (186,'2022_10_25_190934_add_client_id_to_uniques_on_orders_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (187,'2022_10_25_192226_add_unique_for_customer_number_creditor_number_on_contacts_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (188,'2022_10_26_115420_drop_websockets_statistics_entries_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (189,'2022_10_26_115557_drop_record_histories_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (190,'2022_10_26_120443_drop_model_has_external_record_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (191,'2022_10_26_133015_create_datatable_user_settings_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (192,'2022_10_26_134905_adjust_multiple_tables',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (193,'2022_10_27_123200_create_meta_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (194,'2022_11_06_194707_add_is_translatable_to_additional_columns_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (195,'2022_11_11_054334_add_ticket_number_to_tickets_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (196,'2022_11_18_131915_create_product_bundle_product_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (197,'2022_11_18_143148_add_is_bundle_to_products_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (198,'2022_11_21_133802_add_is_bundle_position_to_order_positions_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (199,'2022_11_23_130933_change_count_to_decimal_on_product_bundle_product_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (200,'2022_11_23_132305_create_custom_events_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (201,'2022_12_15_123727_add_expires_at_to_personal_access_tokens_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (202,'2023_01_16_160351_add_shipping_cost_fields_to_orders_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (203,'2023_01_17_102729_add_opening_hours_to_clients_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (204,'2023_01_17_135901_add_is_internal_to_comments',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (205,'2023_01_18_123152_add_authenticatable_to_locks_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (206,'2023_01_20_114233_add_key_to_serial_number_ranges_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (207,'2023_01_24_213752_add_is_frontend_visibible_to_additional_columns_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (208,'2023_01_25_130653_add_order_type_enum_to_order_types_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (209,'2023_01_26_161458_add_totals_to_orders_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (210,'2023_02_06_125912_rename_contact_bank_connections_to_bank_connections',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (211,'2023_02_06_125913_rename_contact_bank_connection_id_to_bank_connection_id_on_sepa_mandates_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (212,'2023_02_06_125914_create_accounts_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (213,'2023_02_06_125915_create_transactions_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (214,'2023_02_08_141252_add_currency_id_to_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (215,'2023_02_08_150617_add_is_default_to_currencies_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (216,'2023_02_14_150828_create_model_related_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (217,'2023_02_22_125128_adjust_order_positions_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (218,'2023_03_30_125128_add_print_layouts_to_order_types_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (219,'2023_04_24_000000_create_additional_column_sets_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (220,'2023_04_24_000000_create_shopware_ids_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (221,'2023_05_05_125128_add_widgets_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (222,'2023_05_17_125128_add_is_highlight_to_products_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (223,'2023_05_22_125128_add_is_active_to_categories_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (224,'2023_06_02_104525_refactor_discounts_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (225,'2023_06_02_133919_create_discount_groups_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (226,'2023_06_02_134118_create_discount_discount_group_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (227,'2023_06_02_134603_create_contact_discount_group_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (228,'2023_06_02_142834_create_contact_discount_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (229,'2023_06_05_083457_add_is_default_to_price_lists_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (230,'2023_06_07_101454_add_parent_id_to_price_lists',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (231,'2023_06_14_101454_add_state_to_projects_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (232,'2023_06_14_161454_add_category_id_to_projects_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (233,'2023_06_14_161454_add_state_to_project_tasks_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (234,'2023_06_30_161454_remove_category_templates_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (235,'2023_07_19_000000_add_auto_increment_to_shopware_ids_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (236,'2023_07_26_130933_change_address_delivery_id_nullable_on_orders_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (237,'2023_07_26_141052_create_work_time_types_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (238,'2023_07_27_125634_create_work_times_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (239,'2023_07_28_090301_create_datev_exports_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (240,'2023_07_28_090311_create_datev_export_lines_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (241,'2023_07_28_131722_create_datev_client_settings_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (242,'2023_07_28_145016_create_datev_client_setting_vat_rates_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (243,'2023_08_03_154428_create_category_price_list_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (244,'2023_08_08_090853_add_debtor_number_to_contacts_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (245,'2023_08_08_090908_add_is_done_to_logs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (246,'2023_08_08_115621_add_expense_account_number_to_datev_client_setting_vat_rates_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (247,'2023_08_14_133924_add_repeatable_calendar_events',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (248,'2023_08_15_075359_add_cover_media_id_to_products_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (249,'2023_08_16_102445_add_export_by_column_to_datev_client_settings_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (250,'2023_08_17_103656_create_ledger_accounts_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (251,'2023_08_17_104532_add_ledger_account_id_to_order_positions_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (252,'2023_08_17_141838_add_posting_key_to_datev_client_setting_vat_rate_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (253,'2023_08_29_144024_create_product_cross_sellings_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (254,'2023_08_29_153011_create_product_cross_selling_product_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (255,'2023_09_24_115947_add_columns_to_contacts_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (256,'2023_09_24_120115_add_footer_text_to_vat_rates_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (257,'2023_09_25_071112_add_bank_connection_id_to_orders_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (258,'2023_09_27_104520_add_is_blacklisted_to_datev_export_lines_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (259,'2023_10_06_163527_rename_changed_route_permissions',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (260,'2023_10_13_110846_add_parent_id_to_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (261,'2023_10_16_104509_create_form_builder_forms',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (262,'2023_10_16_115345_create_role_ticket_type_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (263,'2023_10_17_112549_create_form_builder_sections',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (264,'2023_10_17_113020_create_form_builder_fields',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (265,'2023_10_17_113049_create_form_builder_responses',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (266,'2023_10_17_124523_create_form_builder_field_responses',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (267,'2023_10_18_101819_create_commission_rates_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (268,'2023_10_18_102308_create_commissions_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (269,'2023_10_18_102654_create_paid_commissions_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (270,'2023_10_20_105716_add_agent_id_to_contacts_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (271,'2023_10_20_105940_add_agent_id_and_responsible_user_id_to_orders_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (272,'2023_10_20_110036_create_order_user_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (273,'2023_10_26_133015_add_is_layout_to_datatable_user_settings_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (274,'2023_11_01_082552_add_cache_key_to_datatable_user_settings_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (275,'2023_11_02_192714_create_push_subscriptions_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (276,'2023_11_11_091050_add_name_to_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (277,'2023_11_11_111050_add_name_to_addresses_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (278,'2023_11_13_070701_create_mail_accounts_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (279,'2023_11_13_073208_create_mail_folders_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (280,'2023_11_13_073534_create_mail_messages_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (281,'2023_11_14_102359_create_mailables_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (282,'2023_11_14_102905_create_mail_account_user_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (283,'2023_11_14_203857_change_bank_connections_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (284,'2023_11_14_204030_rename_accounts_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (285,'2023_11_15_101915_create_bank_connection_client_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (286,'2023_11_15_102007_drop_fields_from_clients_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (287,'2023_11_15_131003_change_account_id_on_transactions_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (288,'2023_11_15_133054_rename_bank_connections_on_sepa_mandates_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (289,'2023_11_15_140341_refactor_projects_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (290,'2023_11_20_153207_create_finapi_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (291,'2023_11_23_094503_add_columns_to_work_times_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (292,'2023_11_26_135632_add_address_ids_to_contacts_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (293,'2023_11_26_190436_add_booleans_to_addresses_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (294,'2023_11_28_120317_refactor_project_tasks_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (295,'2023_11_28_124902_create_task_user_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (296,'2023_11_28_125506_create_order_position_task_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (297,'2023_11_28_135337_add_contact_columns_to_addresses_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (298,'2023_11_28_140015_drop_is_primary_on_contact_options_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (299,'2023_11_29_073748_add_on_delete_cascade_to_order_positions',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (300,'2023_12_06_130752_drop_print_data_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (301,'2023_12_06_162906_create_open_ai_files_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (302,'2023_12_06_172524_create_open_ai_chats_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (303,'2023_12_06_180753_create_open_ai_messages_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (304,'2023_12_07_125729_drop_presentations_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (305,'2023_12_07_163545_create_open_ai_assistants_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (306,'2023_12_08_072115_create_schedules_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (307,'2023_12_12_184358_create_open_ai_runs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (308,'2023_12_13_110216_add_mail_columns_to_order_types_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (309,'2023_12_18_131214_create_favorites_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (310,'2023_12_20_090009_create_job_batches_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (311,'2023_12_20_145514_add_is_default_to_tables',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (312,'2023_12_22_090423_add_terms_and_conditions_to_clients_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (313,'2024_01_07_104707_add_is_auto_assign_to_mail_accounts_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (314,'2024_01_07_111301_add_is_active_to_mail_folders',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (315,'2024_01_08_072113_rename_tables_and_columns',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (316,'2024_01_15_133446_add_id_to_product_bundle_product_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (317,'2024_01_16_085808_add_delivery_date_end_and_base_totals_to_orders_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (318,'2024_01_16_144927_add_unit_columns_to_products_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (319,'2024_01_16_162248_add_is_dark_mode_to_users_and_addresses_tables',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (320,'2024_01_17_140634_add_is_billable_to_work_times_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (321,'2024_01_18_182448_add_sepa_columns_to_clients_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (322,'2024_01_18_182644_add_mandate_reference_number_to_sepa_mandates_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (323,'2024_01_22_124107_rename_and_refactor_mail_messages_table_to_communications_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (324,'2024_01_22_124419_rename_and_refactor_mailables_table_to_communicatable_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (325,'2024_01_28_155510_add_booleans_to_payment_types_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (326,'2024_01_29_094425_rename_bank_connection_id_on_orders_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (327,'2024_01_29_112218_create_payment_runs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (328,'2024_01_29_124936_create_order_payment_run_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (329,'2024_01_31_100311_create_payment_reminders_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (330,'2024_01_31_110527_add_payment_reminder_texts_to_payment_types_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (331,'2024_02_05_142329_create_purchase_invoices_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (332,'2024_02_07_015353_add_end_columns_to_schedules_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (333,'2024_02_07_115544_create_product_supplier_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (334,'2024_02_07_120031_drop_columns_from_products_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (335,'2024_02_09_081749_drop_finapi_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (336,'2024_02_09_140651_create_purchase_invoice_positions_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (337,'2024_02_15_141714_add_foreign_key_on_origin_position_id_to_order_positions_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (338,'2024_02_26_130644_update_morph_types_based_on_morph_map',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (339,'2024_03_04_155158_drop_state_settings_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (340,'2024_03_06_090747_add_system_delivery_date_to_purchase_invoices_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (341,'2024_03_06_091108_add_bank_columns_to_orders_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (342,'2024_03_06_125026_add_bank_columns_to_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (343,'2024_03_06_125146_add_bank_columns_to_purchase_invoices_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (344,'2024_03_06_135707_add_lay_out_user_id_to_purchase_invoices_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (345,'2024_03_07_074735_add_purchase_payment_type_id_to_contacts_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (346,'2024_03_07_132542_add_ledger_account_id_to_datev_client_setting_vat_rate_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (347,'2024_03_12_100124_add_is_fibu_transfer_to_datev_client_settings_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (348,'2024_03_12_132456_create_payment_reminder_texts_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (349,'2024_03_12_152551_drop_reminder_texts_from_payment_types_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (350,'2024_03_13_083739_create_datev_client_setting_payment_type_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (351,'2024_03_14_095724_add_payment_reminder_columns_to_orders_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (352,'2024_04_16_114829_update_morph_types_based_on_morph_map',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (353,'2024_04_22_161313_add_parent_id_to_shopware_ids_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (354,'2024_04_25_053928_add_id_to_product_product_option_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (355,'2024_04_25_082035_create_client_product_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (356,'2024_04_26_171408_add_model_morph_to_tasks_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (357,'2024_05_05_110911_add_dto_index_to_shopware_ids_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (358,'2024_05_06_193756_rename_dto_to_entity_on_shopware_ids_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (359,'2024_05_09_191957_remove_unique_login_name_from_addresses_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (360,'2024_05_13_110905_add_foreign_keys_to_orders_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (361,'2024_05_15_100520_add_has_repeatable_events_to_calendars_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (362,'2024_05_16_123152_add_client_id_to_projects_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (363,'2024_05_25_160920_create_queue_monitor_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (364,'2024_05_29_165271_create_queue_monitorables_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (365,'2024_05_29_175541_create_job_batchables_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (366,'2024_06_05_083023_refactor_datev_client_setting_vat_rate_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (367,'2024_06_17_140329_create_carts_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (368,'2024_06_17_140447_create_cart_items_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (369,'2024_06_18_094415_add_order_row_to_widgets_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (370,'2024_06_20_165116_add_rounding_columns_to_price_lists_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (371,'2024_06_30_112522_change_latitude_and_longitude_columns_on_addresses_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (372,'2024_07_10_081037_change_email_columns_on_addresses_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (373,'2024_07_16_081749_change_transactions_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (374,'2024_07_17_170226_add_started_at_and_ended_at_to_communications_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (375,'2024_07_17_181504_add_3cx_extension_to_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (376,'2024_07_22_112004_add_has_formal_salutation_to_addresses_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (377,'2024_07_23_071048_create_address_sanitizer_geocode_results_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (378,'2024_07_23_091048_drop_morph_on_address_sanitizer_geocode_results_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (379,'2024_07_29_092837_add_morph_to_event_subscriptions_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (380,'2024_07_29_115158_add_booleans_to_mail_folders_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (381,'2024_07_29_120146_add_currency_id_to_contacts_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (382,'2024_07_31_133409_add_total_purchase_price_to_orders_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (383,'2024_08_01_045824_add_is_purchase_to_price_lists_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (384,'2024_08_01_062221_add_cost_per_hour_to_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (385,'2024_08_01_062934_add_total_cost_to_trackable_tables',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (386,'2024_08_06_085418_change_default_is_single_booking_on_payment_runs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (387,'2024_08_06_085944_add_is_successful_to_order_payment_run_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (388,'2024_08_06_092837_add_soft_deletes_to_transactions_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (389,'2024_08_06_134434_add_product_type_to_products_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (390,'2024_08_07_130843_create_product_property_groups_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (391,'2024_08_07_130909_update_product_properties_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (392,'2024_08_08_070527_add_vat_id_to_clients_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (393,'2024_08_08_074612_recalculate_total_vats',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (394,'2024_08_08_081423_drop_translatable',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (395,'2024_08_08_121010_fix_foreign_keys_for_created_by',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (396,'2024_08_21_075922_add_user_modification_columns_to_bank_connections_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (397,'2024_08_21_075922_add_user_modification_columns_to_calendar_events_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (398,'2024_08_21_075922_add_user_modification_columns_to_calendars_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (399,'2024_08_21_075922_add_user_modification_columns_to_communications_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (400,'2024_08_21_075922_add_user_modification_columns_to_discount_groups_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (401,'2024_08_21_075922_add_user_modification_columns_to_mail_accounts_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (402,'2024_08_21_075922_add_user_modification_columns_to_payment_reminders_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (403,'2024_08_21_075922_add_user_modification_columns_to_payment_runs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (404,'2024_08_21_075922_add_user_modification_columns_to_purchase_invoice_positions_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (405,'2024_08_21_075922_add_user_modification_columns_to_purchase_invoices_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (406,'2024_08_21_075922_add_user_modification_columns_to_transactions_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (407,'2024_08_21_122117_refactor_created_deleted_updated_by_columns',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (408,'2024_08_23_114601_add_missing_created_updated_deleted_by_columns',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (409,'2024_08_23_114848_drop_created_updated_deleted_by_indexes',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (410,'2024_08_28_163730_migrate_process_subscription_order_job_to_invokable_class_on_schedules_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (411,'2024_08_29_121706_create_contact_origins_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (412,'2024_08_29_124359_add_contact_origin_id_to_contacts_table',1);
