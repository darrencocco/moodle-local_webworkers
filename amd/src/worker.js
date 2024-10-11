define(['core/config'], function(Config) {
    return {
        _prefix: '/local/webworkers/worker.php/' + Config.jsrev + '/',
        getURI: function (scriptName) {
            return Config.wwwroot + this._prefix + scriptName;
        },
        getPath: function (scriptName) {
            return this._prefix + scriptName;
        },
        workerSetupComplete: function() {
            self.requireJSQuirks.handlersReady.resolve();
        },
        activateServiceWorker: function() {
            self.requireJSQuirks.readyToRespond.resolve();
        },
    };
});