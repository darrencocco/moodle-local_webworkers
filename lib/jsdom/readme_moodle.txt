To build JSDOM bundled for Web Workers you will need
to install the nodejs environment listed this directory's
package.json.

Run the browserify-build.js from this directory to build
the bundled and minified versions of the bundle.

Please note that some editing of source files may be
required due to browserify not understanding some newer
node features like the `node:xxx` URL schema.
As of last compile this path had to be edited for that
reason: `node_modules/tough-cookie/dist/cookie/canonicalDomain.js`
The browserify process will inform you of any
errors in the build process.