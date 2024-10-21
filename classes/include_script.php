<?php
namespace local_webworkers;
/**
 * This is probably overkill.
 * @copyright  2024 Darren Cocco
 * @license    http://www.gnu.org/copyleft/lgpl.html GNU LGPL v3 or later
 */
trait include_script {
    /**
     * Wraps a url in an importScripts.
     *
     * @param $url
     * @return string
     */
    protected function include($url) {
        return "importScripts('$url');\n";
    }
}