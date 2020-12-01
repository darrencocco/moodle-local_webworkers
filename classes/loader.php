<?php
namespace local_webworkers;

global $CFG;
require_once("$CFG->libdir/moodlelib.php");
require_once("$CFG->libdir/weblib.php");

class loader {
    /**
     * @var array Inline scripts using RequireJS module loading.
     */
    protected $amdjscode = array();

    public function __construct() {
//        $this->add_logging();
    }

    /**
     * Returns the actual url through which a script is served.
     *
     * @param \moodle_url|string $url full moodle url, or shortened path to script
     * @return \moodle_url
     * @throws \coding_exception
     * @throws \moodle_exception
     */
    protected function js_fix_url($url) {
        global $CFG;

        if ($url instanceof \moodle_url) {
            return $url;
        } else if (strpos($url, '/') === 0) {
            // Fix the admin links if needed.
            if ($CFG->admin !== 'admin') {
                if (strpos($url, "/admin/") === 0) {
                    $url = preg_replace("|^/admin/|", "/$CFG->admin/", $url);
                }
            }
            if (debugging()) {
                // Check file existence only when in debug mode.
                if (!file_exists($CFG->dirroot . strtok($url, '?'))) {
                    throw new coding_exception('Attempt to require a JavaScript file that does not exist.', $url);
                }
            }
            if (substr($url, -3) === '.js') {
                $jsrev = $this->get_jsrev();
                if (empty($CFG->slasharguments)) {
                    return new \moodle_url('/lib/javascript.php', array('rev'=>$jsrev, 'jsfile'=>$url));
                } else {
                    $returnurl = new \moodle_url('/lib/javascript.php');
                    $returnurl->set_slashargument('/'.$jsrev.$url);
                    return $returnurl;
                }
            } else {
                return new \moodle_url($url);
            }
        } else {
            throw new coding_exception('Invalid JS url, it has to be shortened url starting with / or moodle_url instance.', $url);
        }
    }

    /**
     * Returns js code to load amd module loader, then insert inline script tags
     * that contain require() calls using RequireJS.
     * @return string
     * @throws coding_exception
     * @throws \moodle_exception
     */
    public function get_contents() {
        global $CFG;
        $CFG->slasharguments = 1; // FIXME: This is an UGLY hack.
        $output = '';
        $jsrev = $this->get_jsrev();

        $jsloader = new \moodle_url('/lib/javascript.php');
        $jsloader->set_slashargument('/' . $jsrev . '/');
        $requirejsloader = new \moodle_url('/lib/requirejs.php');
        $requirejsloader->set_slashargument('/' . $jsrev . '/');

        $requirejsconfig = file_get_contents($CFG->dirroot . '/lib/requirejs/moodle-config.js');

        // No extension required unless slash args is disabled.
        $jsextension = '.js';
        if (!empty($CFG->slasharguments)) {
            $jsextension = '';
        }

        $requirejsconfig = str_replace('[BASEURL]', $requirejsloader, $requirejsconfig);
        $requirejsconfig = str_replace('[JSURL]', $jsloader, $requirejsconfig);
        $requirejsconfig = str_replace('[JSEXT]', $jsextension, $requirejsconfig);


        $output .= $requirejsconfig;
//        $output .= 'window = self;';
        if ($CFG->debugdeveloper) {
            $output .= 'importScripts(\'' . $this->js_fix_url('/lib/requirejs/require.js')->out_as_local_url() . '\');';
        } else {
            $output .= 'importScripts(\'' . $this->js_fix_url('/lib/requirejs/require.min.js')->out_as_local_url() . '\');';
        }

        // First include must be to a module with no dependencies, this prevents multiple requests.
//        $prefix = "\nrequire(['core/first'], function() {\n";
//        $suffix = "\n});";
//        $output .= $prefix . implode(";\n", $this->amdjscode) . $suffix;
        $output .= implode("\n", $this->amdjscode);
        return $output;
    }

    protected function add_logging() {
        global $CFG;
        // Set the log level for the JS logging.
        $logconfig = new \stdClass();
        $logconfig->level = 'warn';
        if ($CFG->debugdeveloper) {
            $logconfig->level = 'trace';
        }
        $this->js_call_amd('core/log', 'setConfig', array($logconfig));
    }

    /**
     * This function appends a block of code to the AMD specific javascript block executed
     * in the page footer, just after loading the requirejs library.
     *
     * The code passed here can rely on AMD module loading, e.g. require('jquery', function($) {...});
     *
     * @param string $code The JS code to append.
     */
    public function js_amd_inline($code) {
        $this->amdjscode[] = $code;
    }

    /**
     * This function creates a minimal JS script that requires and calls a single function from an AMD module with arguments.
     * If it is called multiple times, it will be executed multiple times.
     *
     * @param string $fullmodule The format for module names is <component name>/<module name>.
     * @param string $func The function from the module to call
     * @param array $params The params to pass to the function. They will be json encoded, so no nasty classes/types please.
     * @throws coding_exception
     */
    public function js_call_amd($fullmodule, $func, $params = array()) {
        global $CFG;

        list($component, $module) = explode('/', $fullmodule, 2);

        $component = \clean_param($component, PARAM_COMPONENT);
        $module = \clean_param($module, PARAM_ALPHANUMEXT);
        $func = \clean_param($func, PARAM_ALPHANUMEXT);

        $jsonparams = array();
        foreach ($params as $param) {
            $jsonparams[] = json_encode($param);
        }
        $strparams = implode(', ', $jsonparams);
        if ($CFG->debugdeveloper) {
            $toomanyparamslimit = 1024;
            if (strlen($strparams) > $toomanyparamslimit) {
                debugging('Too much data passed as arguments to js_call_amd("' . $fullmodule . '", "' . $func .
                    '"). Generally there are better ways to pass lots of data from PHP to JavaScript, for example via Ajax, data attributes, ... . ' .
                    'This warning is triggered if the argument string becomes longer than ' . $toomanyparamslimit . ' characters.', DEBUG_DEVELOPER);
            }
        }

        $js = 'require(["' . $component . '/' . $module . '"], function(amd) { amd.' . $func . '(' . $strparams . '); });';

        $this->js_amd_inline($js);
    }

    /**
     * @param string $fullmodule The format for module names is <component name>/<module name>.
     * @throws coding_exception
     */
    public function add_worker($fullmodule) {
        list($component, $module) = explode('/', $fullmodule, 2);

        $component = clean_param($component, PARAM_COMPONENT);
        $module = clean_param($module, PARAM_ALPHANUMEXT);

        $js = 'require(["' . $component . '/' . $module . '"], function(amd) { return amd });';

        $this->js_amd_inline($js);
    }
    /**
     * Determine the correct JS Revision to use for this load.
     *
     * @return int the jsrev to use.
     */
    protected function get_jsrev() {
        global $CFG;

        if (empty($CFG->cachejs)) {
            $jsrev = -1;
        } else if (empty($CFG->jsrev)) {
            $jsrev = 1;
        } else {
            $jsrev = $CFG->jsrev;
        }

        return $jsrev;
    }
}