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
 * Plugin version and other meta-data are defined here.
 *
 * @package     local_webworkers
 * @copyright   2024 Darren Cocco
 * @license     http://www.gnu.org/copyleft/lgpl.html GNU LGPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/** @var stdClass $plugin */
$plugin->version   = 2024102100;        // The current plugin version (Date: YYYYMMDDXX).
$plugin->requires  = 2022081800;        // Requires this Moodle version.
$plugin->release = '0.2.2';
$plugin->maturity = MATURITY_ALPHA;
$plugin->component = 'local_webworkers';  // Full name of the plugin (used for diagnostics).
