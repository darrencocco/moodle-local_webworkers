<?php
require_once(__DIR__."/../../../../lib/behat/behat_base.php");

class behat_local_webworkers extends behat_base {
    /**
     * @Given /^I'm on the dedicated worker fixture page$/
     */
    public function i_am_on_the_dedicated_worker_fixture_page() {
        $url = "/local/webworkers/tests/behat/fixtures/dedicated_worker.php";
        $this->getSession()->visit($this->locate_path($url));
    }

    /**
     * @Given /^I'm on the shared worker fixture page$/
     */
    public function i_am_on_the_shared_worker_fixture_page() {
        $url = "/local/webworkers/tests/behat/fixtures/shared_worker.php";
        $this->getSession()->visit($this->locate_path($url));
    }
}