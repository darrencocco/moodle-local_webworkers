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
/**
 * A couple of constants to keep things simple.
 *
 * @copyright  2024 Darren Cocco
 * @license    http://www.gnu.org/copyleft/lgpl.html GNU LGPL v3 or later
 * @package    local_webworkers
 */
class constants {
    /**
     * Used as part of the renderer to keep unnecessary junk out.
     */
    const PAGELAYOUT = "embedded";
    /**
     * Special page type for the web worker JS code.
     */
    const PAGETYPE = "web-worker";
}
