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

namespace local_webworkers\output;

defined('MOODLE_INTERNAL') || die();

global $CFG;
if ($CFG->version < 2024100700) {
    class_alias("core_renderer_ajax", "core\\output\\core_renderer_ajax");
}

/**
 * Dummy renderer based on AJAX renderer.
 *
 * @copyright  2024 Darren Cocco
 * @license    http://www.gnu.org/copyleft/lgpl.html GNU LGPL v3 or later
 * @package    local_webworkers
 */
class worker_renderer extends \core\output\core_renderer_ajax {
}
