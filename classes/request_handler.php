<?php

namespace local_webworkers;

require_once("$CFG->libdir/configonlylib.php");

class request_handler {
    public static function decode_request() {
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
        return [$rev, $component, $module];
    }
}