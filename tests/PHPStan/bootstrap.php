<?php

/*
 * The package ships its own auth configuration, but the application PHPStan
 * boots is a plain Laravel skeleton that never loads it. Without this the
 * analysis resolves the authenticated user to the framework default instead of
 * the user model this package actually authenticates.
 */

use Illuminate\Support\Facades\Config;

$auth = require __DIR__ . '/../../config/auth.php';

Config::set('auth.providers', $auth['providers']);
Config::set('auth.guards', $auth['guards']);
