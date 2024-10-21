<?php

namespace local_webworkers\output;

use core_renderer;
use core_useragent;
use stdClass;

/**
 * Dummy renderer based on JS renderer.
 *
 * @copyright  2024 Darren Cocco
 * @license    http://www.gnu.org/copyleft/lgpl.html GNU LGPL v3 or later
 */
class worker_renderer extends core_renderer {

    /**
     * Returns a template fragment representing a fatal error.
     *
     * @param string $message The message to output
     * @param string $moreinfourl URL where more info can be found about the error
     * @param string $link Link for the Continue button
     * @param array $backtrace The execution backtrace
     * @param string $debuginfo Debugging information
     * @return string A template fragment for a fatal error
     */
    public function fatal_error($message, $moreinfourl, $link, $backtrace, $debuginfo = null, $errorcode = "") {
        global $CFG;

        $this->page->set_context(null); // ugly hack - make sure page context is set to something, we do not want bogus warnings here

        $e = new stdClass();
        $e->error = $message;
        $e->errorcode = $errorcode;
        $e->stacktrace = NULL;
        $e->debuginfo = NULL;
        $e->reproductionlink = NULL;
        if (!empty($CFG->debug) and $CFG->debug >= DEBUG_DEVELOPER) {
            $link = (string)$link;
            if ($link) {
                $e->reproductionlink = $link;
            }
            if (!empty($debuginfo)) {
                $e->debuginfo = $debuginfo;
            }
            if (!empty($backtrace)) {
                $e->stacktrace = format_backtrace($backtrace, true);
            }
        }
        $this->header();
        return json_encode($e);
    }

    /**
     * Used to display a notification.
     * For the AJAX notifications are discarded.
     *
     * @param string $message The message to print out.
     * @param string $type The type of notification. See constants on \core\output\notification.
     * @param bool $closebutton Whether to show a close icon to remove the notification (default true).
     */
    public function notification($message, $type = null, $closebutton = true) {
    }

    /**
     * Used to display a redirection message.
     * AJAX redirections should not occur and as such redirection messages
     * are discarded.
     *
     * @param moodle_url|string $encodedurl
     * @param string $message
     * @param int $delay
     * @param bool $debugdisableredirect
     * @param string $messagetype The type of notification to show the message in.
     *         See constants on \core\output\notification.
     */
    public function redirect_message($encodedurl, $message, $delay, $debugdisableredirect,
                                     $messagetype = \core\output\notification::NOTIFY_INFO) {
    }

    /**
     * Prepares the start of an AJAX output.
     */
    public function header() {
        // unfortunately YUI iframe upload does not support application/json
        if (!empty($_FILES)) {
            @header('Content-type: text/plain; charset=utf-8');
            if (!core_useragent::supports_json_contenttype()) {
                @header('X-Content-Type-Options: nosniff');
            }
        } else if (!core_useragent::supports_json_contenttype()) {
            @header('Content-type: text/plain; charset=utf-8');
            @header('X-Content-Type-Options: nosniff');
        } else {
            @header('Content-type: application/json; charset=utf-8');
        }

        // Headers to make it not cacheable and json
        @header('Cache-Control: no-store, no-cache, must-revalidate');
        @header('Cache-Control: post-check=0, pre-check=0', false);
        @header('Pragma: no-cache');
        @header('Expires: Mon, 20 Aug 1969 09:23:00 GMT');
        @header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        @header('Accept-Ranges: none');
    }

    /**
     * There is no footer for an AJAX request, however we must override the
     * footer method to prevent the default footer.
     */
    public function footer() {
    }

    /**
     * No need for headers in an AJAX request... this should never happen.
     * @param string $text
     * @param int $level
     * @param string $classes
     * @param string $id
     */
    public function heading($text, $level = 2, $classes = 'main', $id = null) {
    }
}
