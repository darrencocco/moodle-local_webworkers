<?php

namespace local_webworkers;

defined('MOODLE_INTERNAL') || die();

use core_renderer;
use js_writer;
use moodle_page;
use moodle_url;

/** @var \stdClass $CFG */
require_once("$CFG->libdir/outputrequirementslib.php");
require_once("$CFG->libdir/outputcomponents.php");
/**
 * Page requirements manager designed for web workers.
 * @copyright 2024 Darren Cocco
 * @license http://www.gnu.org/copyleft/lgpl.html GNU LGPL v3 or later
 * @package local_webworkers
 */
class worker_js_manager  extends \page_requirements_manager {

    /**
     * Generates RequireJS configuration block.
     * @return string
     * @throws \coding_exception
     */
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

    /**
     * Transform URL templates for requirejs config.
     * @param string $string
     * @return string
     */
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

    /**
     * Applies similar rules
     * @param string $string
     * @return string
     */
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

    /**
     * Loads the requested AMD module.
     *
     * This is split out as the order of execution
     * isn't ready when the RequireJS configuration
     * is defined.
     *
     * @return string
     */
    public function get_amd_modules() {
        $prefix = <<<EOF
M.util.js_pending("core/first");
require(['core/first'], function() {

EOF;
        if (during_initial_install()) {
            // Do not run a prefetch during initial install as the DB is not available to service WS calls.
            $prefetch = '';
        } else {
            $prefetch = "require(['core/prefetch'])\n";
        }
        $suffix = <<<EOF

    M.util.js_complete("core/first");
});
EOF;
        return $prefix . $prefetch . implode(";\n", $this->amdjscode) . $suffix;
    }

    /**
     * Handles quirk in using RequireJS for module loading in web workers.
     *
     * This chunk of JS helps deal with a problem where events can
     * be sent before the developer supplied event handler is
     * registered.
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

    /**
     * Modified version of the JS header code for workers.
     *
     * @param moodle_page $page
     * @param core_renderer $renderer
     * @return string
     */
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

    /**
     * Inserts the shim to emulate a DOM.
     *
     * Work around for dependencies that can't
     * be disentangled without significant and
     * possibly fundamental changes to Moodle
     * core.
     *
     * @return string
     */
    public function pre_requirejs_dom_shim() {
        $jsdomshim = "[JSURL]local/webworkers/lib/jsdom/jsdom-worker.bundle[JSEXT]";
        $import = $this->include($this->transform_import_urls($jsdomshim));
        return <<<EOF
{$import}
vdom = new jsdom.JSDOM("");

EOF;

    }

    /**
     * Finish setup of emulated DOM.
     *
     * Has to be split up because of load order
     * shenanigans.
     *
     * @return string
     */
    public function post_requirejs_dom_shim() {
        return <<<EOF
document = vdom.window.document;
window = self;

EOF;

    }

    /**
     * JS for importing YUI lib.
     * @return string
     */
    protected function get_yui3lib_headcode() {
        global $CFG;

        $yuiformat = '-min';
        if ($this->yui3loader->filter === 'RAW') {
            $yuiformat = '';
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

    /**
     * JS for importing the javascript-static.js file.
     * @return string
     */
    protected function get_static_js() {
        $staticjs = "[JSURL]lib/javascript-static[JSEXT]";
        return $this->include($this->transform_import_urls($staticjs));
    }

    /**
     * Wraps a url in an importScripts.
     *
     * @param $url
     * @return string
     */
    protected function include($url) {
        return "importScripts('$url');\n";
    }

    protected function static_code_url($rev, $component, $module): moodle_url {
        $url = new moodle_url("/local/webworkers/worker-static.php");
        $url->set_slashargument("/$rev/$component/$module");
        return $url;
    }

    /**
     * Generates the JS for the worker.
     * @param $page
     * @param $renderer
     * @return string
     * @throws \coding_exception
     */
    public function get_cacheable_worker_js() {
        return $this->get_requirejs_quirk_header_code() .
            $this->pre_requirejs_dom_shim() .
            $this->get_requirejs_init() .
            $this->post_requirejs_dom_shim() .
            $this->get_yui3lib_headcode() .
            $this->get_static_js() .
            $this->get_amd_modules();
    }

    public function get_uncacheable_worker_js($page, $renderer, $rev, $component, $module) {
        return $this->get_head_code($page, $renderer) .
            $this->include($this->static_code_url($rev, $component, $module));
    }
}
