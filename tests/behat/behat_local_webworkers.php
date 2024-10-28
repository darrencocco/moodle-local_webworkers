<?php
require_once(__DIR__."/../../../../lib/behat/behat_base.php");

/**
 * Behat steps for local webworkers plugin.
 *
 * @copyright 2024 Darren Cocco
 * @license http://www.gnu.org/copyleft/lgpl.html GNU LGPL v3 or later
 */
class behat_local_webworkers extends behat_base {
    /**
     * Navigates to the dedicated worker fixture page.
     *
     * @Given /^I'm on the dedicated worker fixture page$/
     */
    public function i_am_on_the_dedicated_worker_fixture_page() {
        $url = "/local/webworkers/tests/behat/fixtures/dedicated_worker.php";
        $this->getSession()->visit($this->locate_path($url));
    }

    /**
     * Navigates to the shared worker fixture page.
     *
     * @Given /^I'm on the shared worker fixture page$/
     */
    public function i_am_on_the_shared_worker_fixture_page() {
        $url = "/local/webworkers/tests/behat/fixtures/shared_worker.php";
        $this->getSession()->visit($this->locate_path($url));
    }
}
