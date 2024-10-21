<?php
namespace local_webworkers;
/**
 * A couple of constants to keep things simple.
 *
 * @copyright  2024 Darren Cocco
 * @license    http://www.gnu.org/copyleft/lgpl.html GNU LGPL v3 or later
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
