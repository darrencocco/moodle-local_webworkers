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
define('READ_ONLY_SESSION', true);

require('../../config.php');
global $CFG;
require_once("$CFG->libdir/jslib.php");
require_once("$CFG->libdir/weblib.php");

global $PAGE;
$PAGE->set_pagelayout(\local_webworkers\constants::PAGELAYOUT);
$PAGE->set_pagetype(\local_webworkers\constants::PAGETYPE);
$PAGE->set_context(\context_system::instance());

require_login();

$renderer = $PAGE->get_renderer('local_webworkers', 'worker');

list($rev, $component, $module) = \local_webworkers\request_handler::decode_request();

$workerjsmanager = new \local_webworkers\worker_js_manager();
$content = $workerjsmanager->get_uncacheable_worker_js($PAGE, $renderer, $rev, $component, $module);

js_send_uncached($content, "worker.php");
