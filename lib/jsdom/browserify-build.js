#!/usr/bin/env node
let fs = require("fs");
let browserify = require("browserify");

let bundle = function(writeStream) {
    browserify("jsdom-worker", {standalone: "jsdom"})
        .transform("babelify", {global: true, presets: ["@babel/preset-env"]})
        .bundle()
        .pipe(writeStream);
}

let bundledOutput = fs.createWriteStream("jsdom-worker.bundle.js");
bundle(bundledOutput);

