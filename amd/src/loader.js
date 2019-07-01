define(function() {
    return {
        version: "1.0.0",
        prefix: '/local/webworkers/loader.php/-1/',
        load: function(name, req, onLoad, config) {
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