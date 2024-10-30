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

namespace local_webworkers;

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/configonlylib.php");

/**
 * Common functions for handling incoming requests.
 * @copyright 2024 Darren Cocco
 * @license http://www.gnu.org/copyleft/lgpl.html GNU LGPL v3 or later
 * @package local_webworkers
 */
class request_handler {
    /**
     * Decodes the slash arguments of the current request.
     *
     * @return array has revision, component and module
     */
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
