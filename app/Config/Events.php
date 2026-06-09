<?php

namespace Config;

use CodeIgniter\Events\Events;
use CodeIgniter\Exceptions\FrameworkException;
use CodeIgniter\HotReloader\HotReloader;

/*
 * --------------------------------------------------------------------
 * Application Events
 * --------------------------------------------------------------------
 * Events allow you to tap into the execution of the program without
 * modifying or extending core files. This file provides a central
 * location to define your events, though they can always be added
 * at run-time, also, if needed.
 *
 * You create code that can execute by subscribing to events with
 * the 'on()' method. This accepts any form of callable, including
 * Closures, that will be executed when the event is triggered.
 *
 * Example:
 *      Events::on('create', [$myInstance, 'myMethod']);
 */

/*
 * --------------------------------------------------------------------
 * Apply the salon's configured timezone globally.
 * --------------------------------------------------------------------
 * The "Timezone" setting (Settings → General → salon_timezone) drives every
 * PHP date()/strtotime()/Time call, so the dashboard's "today", agenda, revenue
 * and all stored timestamps follow the salon's local time instead of the
 * server's UTC. Runs for web, API and CLI (cron) requests alike.
 */
Events::on('pre_system', static function (): void {
    $tz = config('App')->appTimezone ?: 'UTC';
    try {
        $saved = (new \App\Modules\Settings\Models\SettingModel())->get('salon_timezone');
        if (is_string($saved) && $saved !== '') {
            $tz = $saved;
        }
    } catch (\Throwable $e) {
        // Settings table not ready (e.g. during migration) — fall back to appTimezone.
    }
    if (in_array($tz, \DateTimeZone::listIdentifiers(), true)) {
        date_default_timezone_set($tz);
        config('App')->appTimezone = $tz;
    }
});

Events::on('pre_system', static function (): void {
    if (ENVIRONMENT !== 'testing') {
        $value = ini_get('zlib.output_compression');

        if (filter_var($value, FILTER_VALIDATE_BOOLEAN) || (int) $value > 0) {
            throw FrameworkException::forEnabledZlibOutputCompression();
        }

        while (ob_get_level() > 0) {
            ob_end_flush();
        }

        ob_start(static fn ($buffer) => $buffer);
    }

    /*
     * --------------------------------------------------------------------
     * Debug Toolbar Listeners.
     * --------------------------------------------------------------------
     * If you delete, they will no longer be collected.
     */
    if (CI_DEBUG && ! is_cli()) {
        Events::on('DBQuery', 'CodeIgniter\Debug\Toolbar\Collectors\Database::collect');
        service('toolbar')->respond();
        // Hot Reload route - for framework use on the hot reloader.
        if (ENVIRONMENT === 'development') {
            service('routes')->get('__hot-reload', static function (): void {
                (new HotReloader())->run();
            });
        }
    }
});
