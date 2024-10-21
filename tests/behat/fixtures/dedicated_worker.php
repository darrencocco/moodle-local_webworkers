<?php
/**
 * Test page for dedicated workers.
 * @copyright 2024 Darren Cocco
 * @license http://www.gnu.org/copyleft/lgpl.html GNU LGPL v3 or later
 */
require_once(__DIR__.'/../../../../../config.php');

// Only continue for behat site.
defined('BEHAT_SITE_RUNNING') ||  die();

$PAGE->set_url('/local/webworkers/tests/behat/fixtures/dedicated_worker.php');
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('admin');

echo $OUTPUT->header();

$PAGE->requires->js_amd_inline(<<<EOL
require(['local_webworkers/test_dedicated_client', 'jquery'], function(dedicatedClient, $) {
  let writeToScreen = function(message) {
    $('#workerresults').append('<p>Message from dedicated worker <br/>' + message.contentsString + '</p>');
  };
  dedicatedClient.init(writeToScreen);
});
EOL
);

echo '<div id="workerresults"><p>Messages echoed back by the dedicated worker.</p></div>';

echo $OUTPUT->footer();
