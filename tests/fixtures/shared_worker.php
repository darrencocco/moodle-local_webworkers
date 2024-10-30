<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Test page for shared workers.
 * @copyright 2024 Darren Cocco
 * @license http://www.gnu.org/copyleft/lgpl.html GNU LGPL v3 or later
 */
require_once(__DIR__.'/../../../../config.php');

// Only continue for behat site.
defined('BEHAT_SITE_RUNNING') ||  die();

$PAGE->set_url('/local/webworkers/tests/fixtures/shared_worker.php');
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('admin');

echo $OUTPUT->header();

$PAGE->requires->js_amd_inline(<<<EOL
require(['local_webworkers/test_shared_client', 'jquery'], function(sharedClient, $) {
  let writeToScreen = function(message) {
    $('#workerresults').append('<p>Message from client ID: ' + message.clientId + '<br/>' + message.contentsString + '</p>');
  };
  sharedClient.init(writeToScreen);
  $("#testform").click(sharedClient.sendMessage.bind(sharedClient));
});
EOL
);
echo '<p><a id="testform" href="#">Send a message</a></p>';
echo '<div id="workerresults"><p>Messages from other tabs.</p></div>';

echo $OUTPUT->footer();
