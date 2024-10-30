<?php
/**
 * Loads a blob of JS for serving a web worker.
 *
 * @copyright 2024 Darren Cocco
 * @license http://www.gnu.org/copyleft/lgpl.html GNU LGPL v3 or later
 * @package local_webworkers
 */
// Disable moodle specific debug messages and any errors in output,
// comment out when debugging or better look into error log!
define('NO_DEBUG_DISPLAY', true);
define('NO_UPGRADE_CHECK', true);  // Ignore upgrade check.
define('NO_MOODLE_COOKIES', true);

require('../../config.php');
global $CFG;
require_once("$CFG->libdir/jslib.php");
require_once("$CFG->libdir/weblib.php");

global $PAGE;
$PAGE->set_pagelayout(\local_webworkers\constants::PAGELAYOUT);
$PAGE->set_pagetype(\local_webworkers\constants::PAGETYPE);
$PAGE->set_context(\context_system::instance());

list($rev, $component, $module) = \local_webworkers\request_handler::decode_request();

$etag = sha1("$rev/$component/$module");

if ($rev > 0) {
    $candidate = $CFG->localcachedir.'/webworkers/'.$etag;

    if (file_exists($candidate)) {
        if (!empty($_SERVER['HTTP_IF_NONE_MATCH']) || !empty($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
            // We do not actually need to verify the etag value because our files
            // never change in cache because we increment the rev parameter.
            js_send_unmodified(filemtime($candidate), $etag);
        }
        js_send_cached($candidate, $etag, 'worker-static.php');
    } else {
        $workerjsmanager = new \local_webworkers\worker_js_manager();
        $workerjsmanager->js_call_amd("$component/$module", "init");
        $content = $workerjsmanager->get_cacheable_worker_js();

        js_write_cache_file_content($candidate, $content);
        // Verify nothing failed in cache file creation.
        clearstatcache();
        if (file_exists($candidate)) {
            js_send_cached($candidate, $etag, 'worker-static.php');
        }
    }
}

$workerjsmanager = new \local_webworkers\worker_js_manager();
$workerjsmanager->js_call_amd("$component/$module", "init");
$content = $workerjsmanager->get_cacheable_worker_js();

js_send_uncached($content, "worker-static.php");
