<?php

// Disable moodle specific debug messages and any errors in output,
// comment out when debugging or better look into error log!
define('NO_DEBUG_DISPLAY', true);

require('../../config.php');
global $CFG;
require_once("$CFG->libdir/jslib.php");
require_once("$CFG->libdir/weblib.php");
require_once("$CFG->libdir/configonlylib.php");

global $PAGE;
$PAGE->set_pagelayout(\local_webworkers\constants::pagelayout);
$PAGE->set_pagetype(\local_webworkers\constants::pagetype);
$PAGE->set_context(\context_system::instance());
$renderer = $PAGE->get_renderer('local_webworkers', 'worker');

if ($slashargument = min_get_slash_argument()) {
    $slashargument = ltrim($slashargument, '/');
    if (substr_count($slashargument, '/') !== 2) {
        header('HTTP/1.0 404 not found');
        die('Slash arguments must contain revision, module and script name');
    }
    // image must be last because it may contain "/"
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

$workerjsmanager = new \local_webworkers\worker_js_manager();
$workerjsmanager->js_call_amd("$component/$module", "init");
$content = $workerjsmanager->get_worker_js($PAGE, $renderer);

js_send_uncached($content, "worker.php");