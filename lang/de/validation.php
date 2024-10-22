<?php

return [
    'accepted' => ':Attribute muss akzeptiert werden.',
    'accepted_if' => ':Attribute muss akzeptiert werden, wenn :other :value ist.',
    'active_url' => ':Attribute ist keine gültige Internet-Adresse.',
    'after' => ':Attribute muss ein Datum nach :date sein.',
    'after_or_equal' => ':Attribute muss ein Datum nach :date oder gleich :date sein.',
    'alpha' => ':Attribute darf nur aus Buchstaben bestehen.',
    'alpha_dash' => ':Attribute darf nur aus Buchstaben, Zahlen, Binde- und Unterstrichen bestehen.',
    'alpha_num' => ':Attribute darf nur aus Buchstaben und Zahlen bestehen.',
    'array' => ':Attribute muss ein Array sein.',
    'ascii' => 'Die :attribute darf nur alphanumerische Single-Byte-Zeichen und -Symbole enthalten.',
    'before' => ':Attribute muss ein Datum vor :date sein.',
    'before_or_equal' => ':Attribute muss ein Datum vor :date oder gleich :date sein.',
    'between' => [
        'array' => ':Attribute muss zwischen :min & :max Elemente haben.',
        'file' => ':Attribute muss zwischen :min & :max Kilobytes groß sein.',
        'numeric' => ':Attribute muss zwischen :min & :max liegen.',
        'string' => ':Attribute muss zwischen :min & :max Zeichen lang sein.',
    ],
    'boolean' => ':Attribute muss entweder \'true\' oder \'false\' sein.',
    'can' => 'Das Feld :attribute enthält einen nicht autorisierten Wert.',
    'confirmed' => ':Attribute stimmt nicht mit der Bestätigung überein.',
    'contains' => 'Dem Feld :attribute fehlt ein erforderlicher Wert.',
    'current_password' => 'Das Passwort ist falsch.',
    'date' => ':Attribute muss ein gültiges Datum sein.',
    'date_equals' => ':Attribute muss ein Datum gleich :date sein.',
    'date_format' => ':Attribute entspricht nicht dem gültigen Format für :format.',
    'decimal' => 'Die :attribute muss :decimal Dezimalstellen haben.',
    'declined' => ':Attribute muss abgelehnt werden.',
    'declined_if' => ':Attribute muss abgelehnt werden wenn :other :value ist.',
    'different' => ':Attribute und :other müssen sich unterscheiden.',
    'digits' => ':Attribute muss :digits Stellen haben.',
    'digits_between' => ':Attribute muss zwischen :min und :max Stellen haben.',
    'dimensions' => ':Attribute hat ungültige Bildabmessungen.',
    'distinct' => ':Attribute beinhaltet einen bereits vorhandenen Wert.',
    'doesnt_end_with' => ':Attribute darf nicht mit einem der folgenden enden: :values.',
    'doesnt_start_with' => ':Attribute darf nicht mit einem der folgenden beginnen: :values.',
    'email' => ':Attribute muss eine gültige E-Mail-Adresse sein.',
    'ends_with' => ':Attribute muss eine der folgenden Endungen aufweisen: :values',
    'enum' => 'Der ausgewählte Wert ist ungültig.',
    'exists' => 'Der gewählte Wert für :attribute ist ungültig.',
    'extensions' => 'Das Feld :attribute muss eine der folgenden Erweiterungen haben: :values.',
    'file' => ':Attribute muss eine Datei sein.',
    'filled' => ':Attribute muss ausgefüllt sein.',
    'gt' => [
        'array' => ':Attribute muss mehr als :value Elemente haben.',
        'file' => ':Attribute muss größer als :value Kilobytes sein.',
        'numeric' => ':Attribute muss größer als :value sein.',
        'string' => ':Attribute muss länger als :value Zeichen sein.',
    ],
    'gte' => [
        'array' => ':Attribute muss mindestens :value Elemente haben.',
        'file' => ':Attribute muss größer oder gleich :value Kilobytes sein.',
        'numeric' => ':Attribute muss größer oder gleich :value sein.',
        'string' => ':Attribute muss mindestens :value Zeichen lang sein.',
    ],
    'hex_color' => 'Das Feld :attribute muss eine gültige Hexadezimalfarbe sein.',
    'image' => ':Attribute muss ein Bild sein.',
    'in' => 'Der gewählte Wert für :attribute ist ungültig.',
    'in_array' => 'Der gewählte Wert für :attribute kommt nicht in :other vor.',
    'integer' => ':Attribute muss eine ganze Zahl sein.',
    'ip' => ':Attribute muss eine gültige IP-Adresse sein.',
    'ipv4' => ':Attribute muss eine gültige IPv4-Adresse sein.',
    'ipv6' => ':Attribute muss eine gültige IPv6-Adresse sein.',
    'json' => ':Attribute muss ein gültiger JSON-String sein.',
    'list' => ':Attribute muss eine Liste sein.',
    'lowercase' => ':Attribute muss in Kleinbuchstaben sein.',
    'lt' => [
        'array' => ':Attribute muss weniger als :value Elemente haben.',
        'file' => ':Attribute muss kleiner als :value Kilobytes sein.',
        'numeric' => ':Attribute muss kleiner als :value sein.',
        'string' => ':Attribute muss kürzer als :value Zeichen sein.',
    ],
    'lte' => [
        'array' => ':Attribute darf maximal :value Elemente haben.',
        'file' => ':Attribute muss kleiner oder gleich :value Kilobytes sein.',
        'numeric' => ':Attribute muss kleiner oder gleich :value sein.',
        'string' => ':Attribute darf maximal :value Zeichen lang sein.',
    ],
    'mac_address' => 'Der Wert muss eine gültige MAC-Adresse sein.',
    'max' => [
        'array' => ':Attribute darf maximal :max Elemente haben.',
        'file' => ':Attribute darf maximal :max Kilobytes groß sein.',
        'numeric' => ':Attribute darf maximal :max sein.',
        'string' => ':Attribute darf maximal :max Zeichen haben.',
    ],
    'max_digits' => ':Attribute darf maximal :max Ziffern lang sein.',
    'mimes' => ':Attribute muss den Dateityp :values haben.',
    'mimetypes' => ':Attribute muss den Dateityp :values haben.',
    'min' => [
        'array' => ':Attribute muss mindestens :min Elemente haben.',
        'file' => ':Attribute muss mindestens :min Kilobytes groß sein.',
        'numeric' => ':Attribute muss mindestens :min sein.',
        'string' => ':Attribute muss mindestens :min Zeichen lang sein.',
    ],
    'min_digits' => ':Attribute muss mindestens :min Ziffern lang sein.',
    'missing' => 'Das Feld :attribute muss fehlen.',
    'missing_if' => 'Das Feld :attribute muss fehlen, wenn :other gleich :value ist.',
    'missing_unless' => 'Das Feld :attribute muss fehlen, es sei denn, :other ist :value.',
    'missing_with' => 'Das Feld :attribute muss fehlen, wenn :values vorhanden ist.',
    'missing_with_all' => 'Das Feld :attribute muss fehlen, wenn :values vorhanden sind.',
    'multiple_of' => ':Attribute muss ein Vielfaches von :value sein.',
    'no_decimals' => ':Attribute darf keine Nachkommastellen haben.',
    'not_in' => 'Der gewählte Wert für :attribute ist ungültig.',
    'not_regex' => ':Attribute hat ein ungültiges Format.',
    'numeric' => ':Attribute muss eine Zahl sein.',
    'password' => [
        'letters' => ':Attribute muss mindestens einen Buchstaben beinhalten.',
        'mixed' => ':Attribute muss mindestens einen Großbuchstaben und einen Kleinbuchstaben beinhalten.',
        'numbers' => ':Attribute muss mindestens eine Zahl beinhalten.',
        'symbols' => ':Attribute muss mindestens ein Sonderzeichen beinhalten.',
        'uncompromised' => ':Attribute wurde in einem Datenleck gefunden. Bitte wählen Sie ein anderes :attribute.',
    ],
    'present' => ':Attribute muss vorhanden sein.',
    'present_if' => 'Das Feld :attribute muss vorhanden sein, wenn :other gleich :value ist.',
    'present_unless' => 'Das Feld :attribute muss vorhanden sein, es sei denn, :other ist :value.',
    'present_with' => 'Das Feld :attribute muss vorhanden sein, wenn :values vorhanden ist.',
    'present_with_all' => 'Das Feld :attribute muss vorhanden sein, wenn :values vorhanden sind.',
    'prohibited' => ':Attribute ist unzulässig.',
    'prohibited_if' => ':Attribute ist unzulässig, wenn :other :value ist.',
    'prohibited_unless' => ':Attribute ist unzulässig, wenn :other nicht :values ist.',
    'prohibits' => ':Attribute verbietet die Angabe von :other.',
    'regex' => ':Attribute Format ist ungültig.',
    'required' => ':Attribute muss ausgefüllt werden.',
    'required_array_keys' => 'Dieses Feld muss Einträge enthalten für: :values.',
    'required_if' => ':Attribute muss ausgefüllt werden, wenn :other den Wert :value hat.',
    'required_if_accepted' => ':Attribute muss ausgefüllt werden, wenn :other gewählt ist.',
    'required_if_declined' => 'Das Feld :attribute ist erforderlich, wenn :other abgelehnt wird.',
    'required_unless' => ':Attribute muss ausgefüllt werden, wenn :other nicht den Wert :values hat.',
    'required_with' => ':Attribute muss ausgefüllt werden, wenn :values ausgefüllt wurde.',
    'required_with_all' => ':Attribute muss ausgefüllt werden, wenn :values ausgefüllt wurde.',
    'required_without' => ':Attribute muss ausgefüllt werden, wenn :values nicht ausgefüllt wurde.',
    'required_without_all' => ':Attribute muss ausgefüllt werden, wenn keines der Felder :values ausgefüllt wurde.',
    'same' => ':Attribute und :other müssen übereinstimmen.',
    'size' => [
        'array' => ':Attribute muss genau :size Elemente haben.',
        'file' => ':Attribute muss :size Kilobyte groß sein.',
        'numeric' => ':Attribute muss gleich :size sein.',
        'string' => ':Attribute muss :size Zeichen lang sein.',
    ],
    'starts_with' => ':Attribute muss mit einem der folgenden Anfänge aufweisen: :values',
    'string' => ':Attribute muss ein String sein.',
    'string_or_integer' => ':Attribute muss ein String oder eine Zahl sein.',
    'string_or_integer_unsigned' => ':Attribute muss ein String oder eine positive Zahl sein.',
    'timezone' => ':Attribute muss eine gültige Zeitzone sein.',
    'ulid' => 'Die :attribute muss eine gültige ULID sein.',
    'unique' => ':Attribute ist bereits vergeben.',
    'uploaded' => ':Attribute konnte nicht hochgeladen werden.',
    'uppercase' => ':Attribute muss in Großbuchstaben sein.',
    'url' => ':Attribute muss eine URL sein.',
    'uuid' => ':Attribute muss ein UUID sein.',

    'attributes' => [
        'abbreviation' => 'Abkürzung',
        'account_holder' => 'Kontoinhaber',
        'addition' => 'Zusatz',
        'address_delivery' => 'Lieferadresse',
        'address_delivery.addition' => 'Zusatz',
        'address_delivery.city' => 'Stadt',
        'address_delivery.company' => 'Firma',
        'address_delivery.email_primary' => 'Primäre E-Mail',
        'address_delivery.firstname' => 'Vorname',
        'address_delivery.id' => 'Lieferadress-ID',
        'address_delivery.lastname' => 'Nachname',
        'address_delivery.latitude' => 'Breitengrad',
        'address_delivery.longitude' => 'Längengrad',
        'address_delivery.mailbox' => 'Postfach',
        'address_delivery.mailbox_city' => 'Postfach Stadt',
        'address_delivery.mailbox_zip' => 'Postfach PLZ',
        'address_delivery.phone' => 'Telefon',
        'address_delivery.salutation' => 'Anrede',
        'address_delivery.street' => 'Straße',
        'address_delivery.title' => 'Titel',
        'address_delivery.url' => 'URL',
        'address_delivery.zip' => 'PLZ',
        'address_delivery_id' => 'Lieferadress-ID',
        'address_id' => 'Adress-ID',
        'address_invoice_id' => 'Rechnungsadress-ID',
        'address_type_code' => 'Adresstypcode',
        'address_types' => 'Adresstypen',
        'address_types.*' => 'Adresstyp',
        'addresses' => 'Adressen',
        'addresses.*.address_id' => 'Adress-ID',
        'addresses.*.address_type_id' => 'Adresstyp-ID',
        'agent_id' => 'Agenten-ID',
        'amount' => 'Menge',
        'amount_bundle' => 'Bündelmenge',
        'amount_packed_products' => 'Menge verpackter Produkte',
        'approval_user_id' => 'Genehmigungsbenutzer-ID',
        'assign' => 'Zuweisen',
        'attachments' => 'Anhänge',
        'attachments.*.categories.*' => 'Kategorie',
        'attachments.*.custom_properties' => 'Benutzerdefinierte Eigenschaften',
        'attachments.*.disk' => 'Festplatte',
        'attachments.*.file_name' => 'Dateiname',
        'attachments.*.media' => 'Medien',
        'attachments.*.mime_type' => 'MIME-Typ',
        'attachments.*.name' => 'Name',
        'authenticatable_id' => 'Authentifizierbare ID',
        'authenticatable_type' => 'Authentifizierbarer Typ',
        'bank_connection_id' => 'Bankverbindung-ID',
        'bank_connections' => 'Bankverbindungen',
        'bank_connections.*' => 'Bankverbindung',
        'bank_name' => 'Bankname',
        'basic_unit' => 'Basiseinheit',
        'bcc' => 'BCC',
        'bic' => 'BIC',
        'booking_date' => 'Buchungsdatum',
        'budget' => 'Budget',
        'bundle_product_id' => 'Bündelprodukt ID',
        'bundle_products' => 'Bündelprodukte',
        'bundle_products.*.count' => 'Bündelprodukt Anzahl',
        'bundle_products.*.id' => 'Bündelprodukt ID',
        'calendar_id' => 'Kalender-ID',
        'can_login' => 'Kann sich einloggen',
        'cart_id' => 'Warenkorb-ID',
        'categories' => 'Kategorien',
        'categories.*' => 'Kategorie',
        'category_id' => 'Kategorie-ID',
        'cc' => 'CC',
        'ceo' => 'Geschäftsführer',
        'channel' => 'Kanal',
        'channel_value' => 'Kanalwert',
        'city' => 'Stadt',
        'client_code' => 'Kundencode',
        'client_id' => 'Kunden-ID',
        'clients' => 'Kunden',
        'clients.*' => 'Kunde',
        'collection' => 'Sammlung',
        'collection_name' => 'Sammlungsname',
        'color' => 'Farbe',
        'columns' => 'Spalten',
        'comment' => 'Kommentar',
        'commission' => 'Provision',
        'commission_rate' => 'Provisionssatz',
        'commission_rate_id' => 'Provisionssatz-ID',
        'communicatable_id' => 'Kommunikationsfähige ID',
        'communicatable_type' => 'Kommunikationsfähiger Typ',
        'communication_type_enum' => 'Kommunikationstyp',
        'company' => 'Firma',
        'confirm_option' => 'Bestätigungsoption',
        'contact_bank_connection_id' => 'Kontaktbankverbindung-ID',
        'contact_id' => 'Kontakt-ID',
        'contact_options' => 'Kontaktoptionen',
        'contact_options.*.id' => 'Kontaktoption ID',
        'contact_options.*.is_primary' => 'Kontaktoption Primär',
        'contact_options.*.label' => 'Kontaktoption Bezeichnung',
        'contact_options.*.type' => 'Kontaktoption Typ',
        'contact_options.*.value' => 'Kontaktoption Wert',
        'count' => 'Anzahl',
        'counterpart_account_number' => 'Gegenpartei Kontonummer',
        'counterpart_bank_name' => 'Gegenpartei Bankname',
        'counterpart_bic' => 'Gegenpartei BIC',
        'counterpart_iban' => 'Gegenpartei IBAN',
        'counterpart_name' => 'Gegenpartei Name',
        'country_id' => 'Länder-ID',
        'cover_media_id' => 'Cover-Medien-ID',
        'credit_limit' => 'Kreditlimit',
        'credit_line' => 'Kreditlinie',
        'creditor_identifier' => 'Gläubigerkennung',
        'creditor_number' => 'Gläubigernummer',
        'cron' => 'Cron',
        'cron.methods' => 'Methoden',
        'cron.methods.basic' => 'Grundlegend',
        'cron.methods.dayConstraint' => 'Tagesbeschränkung',
        'cron.methods.timeConstraint' => 'Zeitbeschränkung',
        'cron.parameters' => 'Parameter',
        'cron.parameters.basic' => 'Grundlegend',
        'cron.parameters.dayConstraint' => 'Tagesbeschränkung',
        'cron.parameters.timeConstraint' => 'Zeitbeschränkung',
        'currency_id' => 'Währungs-ID',
        'current_number' => 'Aktuelle Nummer',
        'custom_properties' => 'Benutzerdefinierte Eigenschaften',
        'customer_delivery_date' => 'Kundenlieferdatum',
        'customer_number' => 'Kundennummer',
        'datanorm_long_text' => 'Datanorm Langtext',
        'date' => 'Datum',
        'date_of_approval' => 'Genehmigungsdatum',
        'date_of_birth' => 'Geburtsdatum',
        'debtor_number' => 'Schuldnernummer',
        'delivery_state' => 'Lieferstatus',
        'department' => 'Abteilung',
        'description' => 'Beschreibung',
        'dimension_height_mm' => 'Höhe in mm',
        'dimension_length_mm' => 'Länge in mm',
        'dimension_width_mm' => 'Breite in mm',
        'discount' => 'Rabatt',
        'discount.discount' => 'Rabatt',
        'discount.is_percentage' => 'Prozentual',
        'discount_days' => 'Rabatt Tage',
        'discount_groups' => 'Rabattgruppen',
        'discount_groups.*' => 'Rabattgruppe',
        'discount_percent' => 'Rabatt Prozent',
        'discount_percentage' => 'Rabattprozentsatz',
        'discounts' => 'Rabatte',
        'discounts.*' => 'Rabatt',
        'discounts.*.discount' => 'Rabatt',
        'discounts.*.is_percentage' => 'Prozentual',
        'discounts.*.sort_number' => 'Sortiernummer',
        'disk' => 'Festplatte',
        'due_at' => 'Fällig am',
        'due_date' => 'Fälligkeitsdatum',
        'ean' => 'EAN',
        'ean_code' => 'EAN-Code',
        'email' => 'E-Mail',
        'email_primary' => 'Primäre E-Mail',
        'encryption' => 'Verschlüsselung',
        'end' => 'Ende',
        'end_date' => 'Enddatum',
        'ended_at' => 'Beendet am',
        'endpoint' => 'Endpunkt',
        'ends_at' => 'Endet am',
        'event' => 'Ereignis',
        'excluded' => 'Ausgeschlossen',
        'excluded.*' => 'Ausgeschlossen',
        'expense_ledger_account_id' => 'Kostenkontenplan-ID',
        'extended_props' => 'Erweiterte Eigenschaften',
        'fax' => 'Fax',
        'field_id' => 'Feld-ID',
        'field_type' => 'Feldtyp',
        'file_name' => 'Dateiname',
        'finish' => 'Fertig',
        'firstname' => 'Vorname',
        'footer' => 'Fußzeile',
        'footer_text' => 'Fußzeilentext',
        'form_id' => 'Formular-ID',
        'from' => 'Von',
        'give' => 'Geben',
        'group' => 'Gruppe',
        'guard_name' => 'Guard-Name',
        'has_delivery_lock' => 'Hat Liefersperre',
        'has_logistic_notify_number' => 'Logistische Benachrichtigungsnummer',
        'has_logistic_notify_phone_number' => 'Logistische Benachrichtigungsnummer',
        'has_repeatable_events' => 'Hat wiederholbare Ereignisse',
        'has_sensitive_reminder' => 'Hat sensible Erinnerung',
        'has_valid_certificate' => 'Gültiges Zertifikat',
        'header' => 'Kopfzeile',
        'header_discount' => 'Kopfzeilenrabatt',
        'host' => 'Host',
        'html' => 'HTML',
        'html_body' => 'HTML-Körper',
        'iban' => 'IBAN',
        'id' => 'ID',
        'instructed_execution_date' => 'Ausführungsdatum',
        'invited_addresses' => 'Eingeladene Adressen',
        'invited_addresses.*.id' => 'Eingeladene Adresse ID',
        'invited_addresses.*.status' => 'Eingeladene Adresse Status',
        'invited_users' => 'Eingeladene Benutzer',
        'invited_users.*.id' => 'Eingeladener Benutzer ID',
        'invited_users.*.status' => 'Eingeladener Benutzer Status',
        'invoice_date' => 'Rechnungsdatum',
        'invoice_number' => 'Rechnungsnummer',
        'is_active' => 'Aktiv',
        'is_active_export_to_web_shop' => 'Aktiv Export zum Webshop',
        'is_all_day' => 'Ganztägig',
        'is_alternative' => 'Alternative',
        'is_anonymous' => 'Anonym',
        'is_auto_assign' => 'Automatisch zugewiesen',
        'is_auto_create_serial_number' => 'Seriennummer automatisch erstellen',
        'is_automatic' => 'Automatisch',
        'is_billable' => 'Abrechenbar',
        'is_broadcast' => 'Übertragung',
        'is_bundle' => 'Bündel',
        'is_bundle_position' => 'Bündelposition',
        'is_confirmed' => 'Bestätigt',
        'is_customer_editable' => 'Kundenbearbeitbar',
        'is_daily_work_time' => 'Tägliche Arbeitszeit',
        'is_default' => 'Standard',
        'is_delivery_address' => 'Lieferadresse',
        'is_direct_debit' => 'Lastschrift',
        'is_eu_country' => 'EU Land',
        'is_free_text' => 'Freitext',
        'is_frontend_visible' => 'Im Frontend sichtbar',
        'is_hidden' => 'Versteckt',
        'is_highlight' => 'Hervorheben',
        'is_imported' => 'Importiert',
        'is_instant_payment' => 'Sofortzahlung',
        'is_internal' => 'Intern',
        'is_invoice_address' => 'Rechnungsadresse',
        'is_locked' => 'Gesperrt',
        'is_main_address' => 'Hauptadresse',
        'is_merge_invoice' => 'Rechnung zusammenführen',
        'is_net' => 'Netto',
        'is_new_customer' => 'Neukunde',
        'is_nos' => 'NOS',
        'is_notifiable' => 'Benachrichtigbar',
        'is_o_auth' => 'OAuth',
        'is_paid' => 'Bezahlt',
        'is_pause' => 'Pause',
        'is_percentage' => 'Prozentual',
        'is_portal_public' => 'Portal Öffentlich',
        'is_pre_filled' => 'Vorgefüllt',
        'is_product_serial_number' => 'Produkt-Seriennummer',
        'is_public' => 'Öffentlich',
        'is_purchase' => 'Kauf',
        'is_required_manufacturer_serial_number' => 'Hersteller-Seriennummer erforderlich',
        'has_serial_numbers' => 'Produkt-Seriennummer erforderlich',
        'is_sales' => 'Verkauf',
        'is_seen' => 'Gesehen',
        'is_service' => 'Dienstleistung',
        'is_shipping_free' => 'Versandkostenfrei',
        'is_sticky' => 'Haftend',
        'is_translatable' => 'Übersetzbar',
        'is_unique' => 'Einzigartig',
        'is_watchlist' => 'Watchlist',
        'iso' => 'ISO',
        'iso_alpha2' => 'ISO Alpha2',
        'iso_alpha3' => 'ISO Alpha3',
        'iso_name' => 'ISO-Name',
        'iso_numeric' => 'ISO Numerisch',
        'key' => 'Schlüssel',
        'keys' => 'Schlüssel',
        'keys.auth' => 'Auth',
        'keys.p256dh' => 'P256dh',
        'label' => 'Bezeichnung',
        'language_code' => 'Sprachcode',
        'language_id' => 'Sprach-ID',
        'lastname' => 'Nachname',
        'latitude' => 'Breitengrad',
        'lay_out_user_id' => 'Benutzer-ID',
        'ledger_account_id' => 'Kontenplan-ID',
        'ledger_account_type_enum' => 'Kontenplan-Typ',
        'length' => 'Länge',
        'logistic_note' => 'Logistische Notiz',
        'longitude' => 'Längengrad',
        'mail_account_id' => 'E-Mail-Konto-ID',
        'mail_accounts' => 'E-Mail-Konten',
        'mail_accounts.*' => 'E-Mail-Konto',
        'mail_body' => 'E-Mail Text',
        'mail_cc' => 'CC',
        'mail_cc.*' => 'CC',
        'mail_folder_id' => 'E-Mail-Ordner-ID',
        'mail_subject' => 'E-Mail Betreff',
        'mail_to' => 'E-Mail an',
        'mail_to.*' => 'E-Mail an',
        'mailbox' => 'Postfach',
        'mailbox_city' => 'Postfach Stadt',
        'mailbox_zip' => 'Postfach PLZ',
        'main_address' => 'Hauptadresse',
        'main_address.addition' => 'Zusatz',
        'main_address.address_types' => 'Adresstypen',
        'main_address.address_types.*' => 'Adresstyp',
        'main_address.can_login' => 'Kann sich einloggen',
        'main_address.city' => 'Stadt',
        'main_address.company' => 'Firma',
        'main_address.contact_options' => 'Kontaktoptionen',
        'main_address.contact_options.*.id' => 'Kontaktoption ID',
        'main_address.contact_options.*.is_primary' => 'Kontaktoption Primär',
        'main_address.contact_options.*.label' => 'Kontaktoption Bezeichnung',
        'main_address.contact_options.*.type' => 'Kontaktoption Typ',
        'main_address.contact_options.*.value' => 'Kontaktoption Wert',
        'main_address.country_id' => 'Länder-ID',
        'main_address.date_of_birth' => 'Geburtsdatum',
        'main_address.department' => 'Abteilung',
        'main_address.email' => 'E-Mail',
        'main_address.email_primary' => 'Primäre E-Mail',
        'main_address.firstname' => 'Vorname',
        'main_address.is_active' => 'Aktiv',
        'main_address.is_delivery_address' => 'Lieferadresse',
        'main_address.is_invoice_address' => 'Rechnungsadresse',
        'main_address.is_main_address' => 'Hauptadresse',
        'main_address.language_id' => 'Sprach-ID',
        'main_address.lastname' => 'Nachname',
        'main_address.latitude' => 'Breitengrad',
        'main_address.longitude' => 'Längengrad',
        'main_address.mailbox' => 'Postfach',
        'main_address.mailbox_city' => 'Postfach Stadt',
        'main_address.mailbox_zip' => 'Postfach PLZ',
        'main_address.password' => 'Passwort',
        'main_address.permissions' => 'Berechtigungen',
        'main_address.permissions.*' => 'Berechtigung',
        'main_address.phone' => 'Telefon',
        'main_address.salutation' => 'Anrede',
        'main_address.street' => 'Straße',
        'main_address.tags' => 'Tags',
        'main_address.tags.*' => 'Tag',
        'main_address.title' => 'Titel',
        'main_address.url' => 'URL',
        'main_address.uuid' => 'UUID',
        'main_address.zip' => 'PLZ',
        'manufacturer_product_number' => 'Hersteller-Produktnummer',
        'margin' => 'Marge',
        'max_delivery_time' => 'Maximale Lieferzeit',
        'max_purchase' => 'Maximalkauf',
        'media' => 'Medien',
        'media.id' => 'Medien-ID',
        'media_id' => 'Medien-ID',
        'media_type' => 'Medientyp',
        'message_id' => 'Nachrichten-ID',
        'message_uid' => 'Nachrichten-UID',
        'migrate' => 'Migrieren',
        'mime_type' => 'MIME-Typ',
        'min_delivery_time' => 'Minimale Lieferzeit',
        'min_purchase' => 'Mindestkauf',
        'model_id' => 'Modell-ID',
        'model_type' => 'Modelltyp',
        'name' => 'Name',
        'notes' => 'Notizen',
        'notification_type' => 'Benachrichtigungstyp',
        'number' => 'Nummer',
        'number_of_packages' => 'Anzahl der Pakete',
        'opening_hours' => 'Öffnungszeiten',
        'options' => 'Optionen',
        'options.*' => 'Option',
        'order_column' => 'Bestellspalte',
        'order_date' => 'Bestelldatum',
        'order_id' => 'Bestell-ID',
        'order_number' => 'Bestellnummer',
        'order_position_id' => 'Bestellposition-ID',
        'order_positions' => 'Bestellpositionen',
        'order_positions.*.amount' => 'Menge',
        'order_positions.*.id' => 'Bestellpositions-ID',
        'order_type_enum' => 'Bestelltyp',
        'order_type_id' => 'Bestelltyp-ID',
        'ordering' => 'Bestellung',
        'orders' => 'Bestellungen',
        'orders.*.amount' => 'Menge',
        'orders.*.order_id' => 'Bestell-ID',
        'origin_position_id' => 'Ursprungsposition-ID',
        'original_start' => 'Ursprünglicher Beginn',
        'packages' => 'Pakete',
        'packages.*' => 'Paket',
        'parameters' => 'Parameter',
        'parent_id' => 'Eltern-ID',
        'password' => 'Passwort',
        'paused_time_ms' => 'Pausenzeit in ms',
        'payment_discount_percent' => 'Rabattprozentsatz',
        'payment_discount_percentage' => 'Zahlungsrabattprozentsatz',
        'payment_discount_target' => 'Rabattziel',
        'payment_reminder_current_level' => 'Aktuelles Mahnstufe',
        'payment_reminder_days_1' => 'Zahlungserinnerungstag 1',
        'payment_reminder_days_2' => 'Zahlungserinnerungstag 2',
        'payment_reminder_days_3' => 'Zahlungserinnerungstag 3',
        'payment_reminder_email_text' => 'Zahlungserinnerungs-E-Mail',
        'payment_reminder_next_date' => 'Nächstes Mahndatum',
        'payment_reminder_text' => 'Zahlungserinnerungstext',
        'payment_run_type_enum' => 'Zahlungslauf-Typ',
        'payment_state' => 'Zahlungsstatus',
        'payment_target' => 'Zahlungsziel',
        'payment_target_days' => 'Zahlungsziel in Tagen',
        'payment_texts' => 'Zahlungstexte',
        'payment_type_id' => 'Zahlungstyp-ID',
        'permissions' => 'Berechtigungen',
        'permissions.*' => 'Berechtigung',
        'phone' => 'Telefon',
        'port' => 'Port',
        'possible_delivery_date' => 'Mögliches Lieferdatum',
        'postcode' => 'Postleitzahl',
        'posting' => 'Buchung',
        'posting_account' => 'Buchungskonto',
        'prefix' => 'Präfix',
        'preview' => 'Vorschau',
        'price' => 'Preis',
        'price_id' => 'Preis-ID',
        'price_list_code' => 'Preisliste Code',
        'price_list_id' => 'Preisliste-ID',
        'prices' => 'Preise',
        'prices.*.price' => 'Preis',
        'prices.*.price_list_id' => 'Preisliste-ID',
        'print_layouts' => 'Drucklayouts',
        'print_layouts.*' => 'Drucklayout',
        'priority' => 'Priorität',
        'product_cross_sellings.*.id' => 'Produktquersellings ID',
        'product_cross_sellings.*.is_active' => 'Aktiv',
        'product_cross_sellings.*.name' => 'Name',
        'product_cross_sellings.*.order_column' => 'Bestellspalte',
        'product_cross_sellings.*.products' => 'Produkte',
        'product_cross_sellings.*.products.*' => 'Produkt',
        'product_cross_sellings.*.uuid' => 'UUID',
        'product_id' => 'Produkt-ID',
        'product_number' => 'Produktnummer',
        'product_option_group_id' => 'Produktoptionsgruppen-ID',
        'product_options' => 'Produktoptionen',
        'product_options.*' => 'Produktoption',
        'product_options.*.*' => 'Produktoption',
        'product_options.*.id' => 'Produktoption ID',
        'product_options.*.name' => 'Produktoption Name',
        'product_properties' => 'Produkteigenschaften',
        'product_properties.*.id' => 'Produkteigenschaft ID',
        'product_properties.*.value' => 'Produkteigenschaft Wert',
        'products' => 'Produkte',
        'products.*' => 'Produkt',
        'progress' => 'Fortschritt',
        'project_id' => 'Projekt-ID',
        'project_number' => 'Projektnummer',
        'protocol' => 'Protokoll',
        'provision' => 'Provision',
        'purchase_invoice_id' => 'Kaufrechnung-ID',
        'purchase_invoice_positions' => 'Kaufrechnungspositionen',
        'purchase_invoice_positions.*.amount' => 'Menge',
        'purchase_invoice_positions.*.id' => 'ID',
        'purchase_invoice_positions.*.ledger_account_id' => 'Kontenplan-ID',
        'purchase_invoice_positions.*.name' => 'Name',
        'purchase_invoice_positions.*.product_id' => 'Produkt-ID',
        'purchase_invoice_positions.*.total_price' => 'Gesamtpreis',
        'purchase_invoice_positions.*.unit_price' => 'Einheitspreis',
        'purchase_invoice_positions.*.uuid' => 'UUID',
        'purchase_invoice_positions.*.vat_rate_id' => 'Mehrwertsteuersatz-ID',
        'purchase_invoice_positions.*amount' => 'Menge',
        'purchase_invoice_positions.*id' => 'Kaufrechnungsposition ID',
        'purchase_invoice_positions.*ledger_account_id' => 'Kontenplan-ID',
        'purchase_invoice_positions.*name' => 'Name',
        'purchase_invoice_positions.*product_id' => 'Produkt-ID',
        'purchase_invoice_positions.*total_price' => 'Gesamtpreis',
        'purchase_invoice_positions.*unit_price' => 'Einheitspreis',
        'purchase_invoice_positions.*vat_rate_id' => 'Mehrwertsteuersatz-ID',
        'purchase_payment_type_id' => 'Kaufzahlungstyp-ID',
        'purchase_price' => 'Einkaufspreis',
        'purchase_steps' => 'Kaufschritte',
        'purchase_unit_id' => 'Kaufeinheiten-ID',
        'purpose' => 'Zweck',
        'rate_percentage' => 'Satzprozentsatz',
        'recurrences' => 'Wiederholungen',
        'reference_unit_id' => 'Referenzeinheiten-ID',
        'reminder_body' => 'Erinnerungstext',
        'reminder_level' => 'Erinnerungsstufe',
        'reminder_subject' => 'Erinnerungsbetreff',
        'repeat' => 'Wiederholen',
        'repeat.interval' => 'Intervall',
        'repeat.monthly' => 'Monatlich',
        'repeat.unit' => 'Einheit',
        'repeat.weekdays' => 'Wochentage',
        'repeat.weekdays.*' => 'Wochentag',
        'repeat_end' => 'Wiederholungsende',
        'requires_approval' => 'Genehmigung erforderlich',
        'requires_manual_transfer' => 'Manuelle Übertragung erforderlich',
        'response' => 'Antwort',
        'response_id' => 'Antwort-ID',
        'responsible_user_id' => 'Verantwortlicher Benutzer-ID',
        'restock_time' => 'Wiederauffüllzeit',
        'roles' => 'Rollen',
        'roles.*' => 'Rolle',
        'rollback' => 'Zurücksetzen',
        'rounding_method_enum' => 'Rundungsmethode',
        'rounding_mode' => 'Rundungsmodus',
        'rounding_number' => 'Rundungsnummer',
        'rounding_precision' => 'Rundungspräzision',
        'salutation' => 'Anrede',
        'section_id' => 'Abschnitt-ID',
        'selling_unit' => 'Verkaufseinheit',
        'seo_keywords' => 'SEO-Schlüsselwörter',
        'sepa_text' => 'SEPA-Text',
        'serial_number' => 'Seriennummer',
        'serial_number_range_id' => 'Seriennummernbereich-ID',
        'session_id' => 'Sitzungs-ID',
        'settings' => 'Einstellungen',
        'shipping_costs_net_price' => 'Versandkosten Nettopreis',
        'signed_date' => 'Unterschriftsdatum',
        'simulate' => 'Simulieren',
        'slug' => 'Slug',
        'smtp_email' => 'SMTP-E-Mail',
        'smtp_encryption' => 'SMTP-Verschlüsselung',
        'smtp_host' => 'SMTP-Host',
        'smtp_mailer' => 'SMTP-Mailer',
        'smtp_password' => 'SMTP-Passwort',
        'smtp_port' => 'SMTP-Port',
        'sort_number' => 'Sortiernummer',
        'start' => 'Beginn',
        'start_date' => 'Startdatum',
        'start_number' => 'Startnummer',
        'started_at' => 'Begonnen am',
        'state' => 'Zustand',
        'stock' => 'Bestand',
        'stores_serial_numbers' => 'Speichert Seriennummern',
        'street' => 'Straße',
        'subject' => 'Betreff',
        'suffix' => 'Suffix',
        'supplier_contact_id' => 'Lieferantenkontakt-ID',
        'suppliers' => 'Lieferanten',
        'suppliers.*.contact_id' => 'Lieferantenkontakt-ID',
        'suppliers.*.manufacturer_product_number' => 'Hersteller-Produktnummer',
        'suppliers.*.purchase_price' => 'Kaufpreis',
        'symbol' => 'Symbol',
        'sync' => 'Synchronisieren',
        'system_delivery_date' => 'Systemlieferdatum',
        'system_delivery_date_end' => 'Systemlieferdatum Ende',
        'tags' => 'Tags',
        'tags.*' => 'Tag',
        'terms_and_conditions' => 'Allgemeine Geschäftsbedingungen',
        'text' => 'Text',
        'text_body' => 'Textkörper',
        'ticket_id' => 'Ticket-ID',
        'ticket_type_id' => 'Tickettyp-ID',
        'till' => 'Bis',
        'time_budget' => 'Zeitbudget',
        'time_unit_enum' => 'Zeiteinheit',
        'title' => 'Titel',
        'to' => 'An',
        'total_net_price' => 'Gesamtnettopreis',
        'total_price' => 'Gesamtpreis',
        'trackable_id' => 'Nachverfolgbare ID',
        'trackable_type' => 'Nachverfolgbare Typ',
        'tracking_email' => 'Verfolgungs-E-Mail',
        'type' => 'Typ',
        'unit_gram_weight' => 'Einheitsgewicht in Gramm',
        'unit_id' => 'Einheiten-ID',
        'unit_price' => 'Einheitspreis',
        'unit_price_price_list_id' => 'Einheitspreisliste-ID',
        'url' => 'URL',
        'user_code' => 'Benutzercode',
        'user_id' => 'Benutzer-ID',
        'users' => 'Benutzer',
        'users.*' => 'Benutzer',
        'uuid' => 'UUID',
        'validations' => 'Validierungen',
        'validations.*' => 'Validierungen',
        'value' => 'Wert',
        'value_date' => 'Wertstellungsdatum',
        'values' => 'Werte',
        'vat_id' => 'USt-ID',
        'vat_rate_id' => 'Mehrwertsteuersatz-ID',
        'vendor_customer_number' => 'Lieferantenkundennummer',
        'view' => 'Ansicht',
        'warehouse_id' => 'Lager-ID',
        'warning_stock_amount' => 'Warnbestand',
        'website' => 'Webseite',
        'weight_gram' => 'Gewicht in Gramm',
        'work_time_type_id' => 'Arbeitszeittyp-ID',
        'zip' => 'PLZ',
    ],
];
