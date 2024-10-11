/**
 * @deprecated Since Moodle 4.1
 */
define(function() {
    return {
        version: "1.0.0",
        prefix: '/local/webworkers/loader.php/-1/',
        deprecatedMessage: 'The local_webworkers/loader module has been deprecated since 16/8/24 '
            + 'and will be removed in Moodle 6.0',
        load: function(name, req, onLoad, config) {
            window.console.log(this.deprecatedMessage);
            if (config.isBuild) {
                // Don't do anything if this is a build, can't inline a web worker
                onLoad();
                return;
            }

            var url = this.prefix + name;

            onLoad(new Worker(url));
        }
    };
});