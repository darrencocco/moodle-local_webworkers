#!/usr/bin/env node
var fs = require("fs");
var browserify = require("browserify");
const terser = require("terser");

let minifyBundle = function() {
    terser.minify({"jsdom-worker.bundle.js": fs.readFileSync("jsdom-worker.bundle.js", "utf8")}, {})
        .then(function(minifiedCode) {
            fs.writeFileSync(
                "jsdom-worker.bundle.min.js",
                minifiedCode.code
            );
        });
}

let bundle = function(writeStream) {
    browserify("jsdom-worker", {standalone: "jsdom"})
        .transform("babelify", {global: true, presets: ["@babel/preset-env"]})
        .bundle()
        .pipe(writeStream);
}

let bundledOutput = fs.createWriteStream("jsdom-worker.bundle.js");
bundledOutput.on('close', minifyBundle);
bundle(bundledOutput);

