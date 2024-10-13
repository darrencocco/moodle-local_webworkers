<?php
namespace local_webworkers;

use core_renderer;
use js_writer;
use moodle_page;
use moodle_url;

/** @var \stdClass $CFG */
require_once("$CFG->libdir/outputrequirementslib.php");
require_once("$CFG->libdir/outputcomponents.php");
class worker_js_manager  extends \page_requirements_manager {
    use include_script;
    public function get_requirejs_init() {
        global $CFG;
        // We will cache JS if cachejs is not set, or it is true.
        $cachejs = !isset($CFG->cachejs) || $CFG->cachejs;

        $requirejsconfig = file_get_contents($CFG->dirroot . '/lib/requirejs/moodle-config.js');

        $output = $this->transform_requirejs_urls($requirejsconfig);
        if ($cachejs) {
            $output .= $this->include($this->js_fix_url('/lib/requirejs/require.min.js'));
        } else {
            $output .= $this->include($this->js_fix_url('/lib/requirejs/require.js'));
        }

        return $output;
    }

    protected function transform_requirejs_urls($string) {
        global $CFG;
        // We will cache JS if cachejs is not set, or it is true.
        $cachejs = !isset($CFG->cachejs) || $CFG->cachejs;

        $jsrev = $this->get_jsrev();

        // No extension required unless slash args is disabled.
        $jsextension = '.js';
        if (!empty($CFG->slasharguments)) {
            $jsextension = '';
        }

        $minextension = '.min';
        if (!$cachejs) {
            $minextension = '';
        }

        $jsloader = new moodle_url('/lib/javascript.php');
        $jsloader->set_slashargument('/' . $jsrev . '/');
        $requirejsloader = new moodle_url('/lib/requirejs.php');
        $requirejsloader->set_slashargument('/' . $jsrev . '/');

        $string = str_replace('[BASEURL]', $requirejsloader, $string);
        $string = str_replace('[JSURL]', $jsloader, $string);
        $string = str_replace('[JSMIN]', $minextension, $string);
        $string = str_replace('[JSEXT]', $jsextension, $string);

        return $string;
    }

    protected function transform_import_urls($string) {
        global $CFG;
        // We will cache JS if cachejs is not set, or it is true.
        $cachejs = !isset($CFG->cachejs) || $CFG->cachejs;

        $jsrev = $this->get_jsrev();

        // No extension required unless slash args is disabled.
        $jsextension = '.js';

        $minextension = '.min';
        if (!$cachejs) {
            $minextension = '';
        }

        $jsloader = new moodle_url('/lib/javascript.php');
        $jsloader->set_slashargument('/' . $jsrev . '/');
        $requirejsloader = new moodle_url('/lib/requirejs.php');
        $requirejsloader->set_slashargument('/' . $jsrev . '/');

        $string = str_replace('[BASEURL]', $requirejsloader, $string);
        $string = str_replace('[JSURL]', $jsloader, $string);
        $string = str_replace('[JSMIN]', $minextension, $string);
        $string = str_replace('[JSEXT]', $jsextension, $string);

        return $string;
    }
    public function get_amd_modules() {
        if (during_initial_install()) {
            // Do not run a prefetch during initial install as the DB is not available to service WS calls.
            $prefetch = '';
        } else {
            $prefetch = "require(['core/prefetch'])\n";
        }
        return $prefetch . implode(";\n", $this->amdjscode);
    }

    /**
     * Handles quirk in using RequireJS for module loading in web workers.
     *
     * This chunk of JS helps deal with a problem where the first
     * connect event seems to be sent before the developer supplied
     * event handler.
     *
     * @return string
     */
    public function get_requirejs_quirk_header_code() {
        return <<<EOF
self.unansweredConnectRequests = [];
self.unansweredInstallRequest;
self.unansweredActivateRequest;
self.unansweredMessages = [];
let quirkConnect = function(e) {
    self.unansweredConnectRequests.push(e);
}
let quirkInstall = function(e) {
    e.waitUntil(self.requireJSQuirks.handlersReady);
    self.unansweredInstallRequest = e;
}
let quirkActivate = function(e) {
    e.waitUntil(self.requireJSQuirks.readyToRespond);
    self.unansweredActivateRequest = e;
}
let quirkMessage = function(e) {
    self.unansweredMessages.push(e);
    console.log(e);
}

let handlersReadyResolve, handlersReadyReject;
let readyToRespondResolve, readyToRespondReject;

self.requireJSQuirks = {
    handlersReady: new Promise(function executor(resolve, reject) {
        self.addEventListener('connect', quirkConnect);
        self.addEventListener('install', quirkInstall);
        self.addEventListener('message', quirkMessage);
        handlersReadyResolve =  resolve;
        handlersReadyReject = reject;
    }),
    readyToRespond: new Promise(function (resolve, reject) {
        self.addEventListener('activate', quirkActivate);
        readyToRespondResolve = resolve;
        readyToRespondReject = reject;
    }),
};
self.requireJSQuirks.handlersReady.resolve = handlersReadyResolve;
self.requireJSQuirks.handlersReady.reject = handlersReadyReject;
self.requireJSQuirks.readyToRespond.resolve = readyToRespondResolve;
self.requireJSQuirks.readyToRespond.reject = readyToRespondReject;

self.requireJSQuirks.handlersReady.then(function() {
    self.removeEventListener('connect', quirkConnect);
    self.removeEventListener('install', quirkInstall);
    self.removeEventListener('message', quirkMessage);
    self.unansweredConnectRequests.forEach(function(e) {
        self.dispatchEvent(e);
    });
    self.unansweredMessages.forEach(function(e) {
        self.dispatchEvent(e);
    })
});
self.requireJSQuirks.readyToRespond.then(function() {
    self.removeEventListener('activate', quirkActivate);
});

EOF;

    }

    public function get_head_code(moodle_page $page, core_renderer $renderer) {
        global $CFG;

        // Note: the $page and $output are not stored here because it would
        // create circular references in memory which prevents garbage collection.
        $this->init_requirements_data($page, $renderer);

        // Set up the M namespace.
        $js = "var M = {}; M.yui = {};\n";

        // Capture the time now ASAP during page load. This minimises the lag when
        // we try to relate times on the server to times in the browser.
        // An example of where this is used is the quiz countdown timer.
        $js .= "M.pageloadstarttime = new Date();\n";

        // Add a subset of Moodle configuration to the M namespace.
        $js .= js_writer::set_variable('M.cfg', $this->M_cfg, false) . "\n";

        return $js;
    }

    public function pre_requirejs_dom_shim() {
        $jsdomshim = "[JSURL]local/webworkers/jsdom/jsdom-worker.bundle[JSMIN][JSEXT]";
        $import = $this->include($this->transform_import_urls($jsdomshim));
        return <<<EOF
${import}
vdom = new jsdom.JSDOM("");

EOF;

    }
    public function post_requirejs_dom_shim() {
        return <<<EOF
document = vdom.window.document;
window = self;

EOF;

    }

    public function js_call_amd($fullmodule, $func = null, $params = array()) {
        global $CFG;

        $modulepath = explode('/', $fullmodule);

        $modname = clean_param(array_shift($modulepath), PARAM_COMPONENT);
        foreach ($modulepath as $module) {
            $modname .= '/' . clean_param($module, PARAM_ALPHANUMEXT);
        }

        $functioncode = [];
        if ($func !== null) {
            $func = clean_param($func, PARAM_ALPHANUMEXT);

            $jsonparams = array();
            foreach ($params as $param) {
                $jsonparams[] = json_encode($param);
            }
            $strparams = implode(', ', $jsonparams);
            if ($CFG->debugdeveloper) {
                $toomanyparamslimit = 1024;
                if (strlen($strparams) > $toomanyparamslimit) {
                    debugging('Too much data passed as arguments to js_call_amd("' . $fullmodule . '", "' . $func .
                        '"). Generally there are better ways to pass lots of data from PHP to JavaScript, for example via Ajax, ' .
                        'data attributes, ... . This warning is triggered if the argument string becomes longer than ' .
                        $toomanyparamslimit . ' characters.', DEBUG_DEVELOPER);
                }
            }

            $functioncode[] = "amd.{$func}({$strparams});";
        }

        $initcode = implode(' ', $functioncode);
        $js = "require(['{$modname}'], function(amd) {{$initcode}});";

        $this->js_amd_inline($js);
    }

    protected function get_yui3lib_headcode() {
        global $CFG;

        $jsrev = $this->get_jsrev();

        $yuiformat = '-min';
        if ($this->yui3loader->filter === 'RAW') {
            $yuiformat = '';
        }

        $format = '-min';
        if ($this->YUI_config->groups['moodle']['filter'] === 'DEBUG') {
            $format = '-debug';
        }

        $rollupversion = $CFG->yui3version;
        if (!empty($CFG->yuipatchlevel)) {
            $rollupversion .= '_' . $CFG->yuipatchlevel;
        }

        $baserollups = array(
            'rollup/' . $rollupversion . "/yui-moodlesimple{$yuiformat}.js",
        );

        if ($this->yui3loader->combine) {
            return $this->include(
                $this->yui3loader->local_comboBase .
                implode('&amp;', $baserollups));
        } else {
            $code = '';
            foreach ($baserollups as $rollup) {
                $code .= $this->include($this->yui3loader->local_comboBase.$rollup);
            }
            return $code;
        }

    }

    protected function get_static_js() {
        $staticjs = "[JSURL]lib/javascript-static[JSEXT]";
        return $this->include($this->transform_import_urls($staticjs));
    }

    public function get_worker_js($page, $renderer) {
        return $this->get_requirejs_quirk_header_code() .
            $this->pre_requirejs_dom_shim() .
            $this->get_requirejs_init() .
            $this->post_requirejs_dom_shim() .
            $this->get_head_code($page, $renderer) .
            $this->get_yui3lib_headcode() .
            $this->get_static_js() .
            $this->get_amd_modules();
    }
}