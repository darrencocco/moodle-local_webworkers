<?php
/**
 * Loads a blob of JS for serving a web worker.
 * @copyright 2024 Darren Cocco
 * @license http://www.gnu.org/copyleft/lgpl.html GNU LGPL v3 or later
 */
// Disable moodle specific debug messages and any errors in output,
// comment out when debugging or better look into error log!
define('NO_DEBUG_DISPLAY', true);

require('../../config.php');
global $CFG;
require_once("$CFG->libdir/jslib.php");
require_once("$CFG->libdir/weblib.php");
require_once("$CFG->libdir/configonlylib.php");

global $PAGE;
$PAGE->set_pagelayout(\local_webworkers\constants::PAGELAYOUT);
$PAGE->set_pagetype(\local_webworkers\constants::PAGETYPE);
$PAGE->set_context(\context_system::instance());
$renderer = $PAGE->get_renderer('local_webworkers', 'worker');

if ($slashargument = min_get_slash_argument()) {
    $slashargument = ltrim($slashargument, '/');
    if (substr_count($slashargument, '/') !== 2) {
        header('HTTP/1.0 404 not found');
        die('Slash arguments must contain revision, module and script name');
    }
    list($rev, $component, $module) = explode('/', $slashargument, 3);
    $rev  = min_clean_param($rev, 'INT');
    $component = min_clean_param($component, 'SAFEDIR');
    $module  = min_clean_param($module, 'SAFEDIR');
} else {
    die('No slash arguments provided');
}

if (!min_is_revision_valid_and_current($rev)) {
    // If the rev is invalid, normalise it to -1 to disable all caching.
    $rev = -1;
}
$etag = sha1("$rev/$component/$module");

if ($rev > 0) {
    $candidate = $CFG->localcachedir.'/webworkers/'.$etag;

    if (file_exists($candidate)) {
        if (!empty($_SERVER['HTTP_IF_NONE_MATCH']) || !empty($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
            // we do not actually need to verify the etag value because our files
            // never change in cache because we increment the rev parameter
            js_send_unmodified(filemtime($candidate), $etag);
        }
        js_send_cached($candidate, $etag);
    } else {
        // The JS needs minfifying, so we're gonna have to load our full Moodle
        // environment to process it..
        define('ABORT_AFTER_CONFIG_CANCEL', true);

        define('NO_MOODLE_COOKIES', true); // Session not used here.
        define('NO_UPGRADE_CHECK', true);  // Ignore upgrade check.

        require("$CFG->dirroot/lib/setup.php");

        $workerjsmanager = new \local_webworkers\worker_js_manager();
        $workerjsmanager->js_call_amd("$component/$module", "init");
        $content = $workerjsmanager->get_worker_js($PAGE, $renderer);

        js_write_cache_file_content($candidate, $content);
        // verify nothing failed in cache file creation
        clearstatcache();
        if (file_exists($candidate)) {
            js_send_cached($candidate, $etag);
        }
    }
}

$workerjsmanager = new \local_webworkers\worker_js_manager();
$workerjsmanager->js_call_amd("$component/$module", "init");
$content = $workerjsmanager->get_worker_js($PAGE, $renderer);

js_send_uncached($content, "worker.php");
