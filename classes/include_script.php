<?php
namespace local_webworkers;

trait include_script {
    function include($url) {
        return "importScripts('$url');\n";
    }
}