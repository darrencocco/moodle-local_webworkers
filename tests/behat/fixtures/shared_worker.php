<?php
/**
 * Test page for shared workers.
 * @copyright 2024 Darren Cocco
 * @license http://www.gnu.org/copyleft/lgpl.html GNU LGPL v3 or later
 */
require_once(__DIR__.'/../../../../../config.php');

// Only continue for behat site.
defined('BEHAT_SITE_RUNNING') ||  die();

$PAGE->set_url('/local/webworkers/tests/behat/fixtures/shared_worker.php');
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('admin');

echo $OUTPUT->header();

$PAGE->requires->js_amd_inline(<<<EOL
require(['local_webworkers/test_shared_client', 'jquery'], function(sharedClient, $) {
  let writeToScreen = function(message) {
    $('#workerresults').append('<p>Message from client ID: ' + message.clientId + '<br/>' + message.contentsString + '</p>');
  };
  sharedClient.init(writeToScreen);
});
EOL
);

echo '<div id="workerresults"><p>Messages from other tabs.</p></div>';

echo $OUTPUT->footer();
