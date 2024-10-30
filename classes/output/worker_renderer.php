<?php

namespace local_webworkers\output;

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
