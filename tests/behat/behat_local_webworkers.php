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

require_once(__DIR__."/../../../../lib/behat/behat_base.php");

/**
 * Behat steps for local webworkers plugin.
 *
 * @copyright 2024 Darren Cocco
 * @license http://www.gnu.org/copyleft/lgpl.html GNU LGPL v3 or later
 * @package local_webworkers
 */
class behat_local_webworkers extends behat_base {
    /**
     * Navigates to the dedicated worker fixture page.
     *
     * @Given /^I'm on the dedicated worker fixture page$/
     */
    public function i_am_on_the_dedicated_worker_fixture_page() {
        $url = "/local/webworkers/tests/fixtures/dedicated_worker.php";
        $this->getSession()->visit($this->locate_path($url));
    }

    /**
     * Navigates to the shared worker fixture page.
     *
     * @Given /^I'm on the shared worker fixture page$/
     */
    public function i_am_on_the_shared_worker_fixture_page() {
        $url = "/local/webworkers/tests/fixtures/shared_worker.php";
        $this->getSession()->visit($this->locate_path($url));
    }
}
