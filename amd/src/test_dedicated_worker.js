define(['local_webworkers/web_worker'], function(webWorker) {
    return {
        init: function() {
            self.addEventListener('message', function(e) {
                self.postMessage({
                    type: 'TextMessage',
                    contentsString: e.data.contentsString,
                });
            });
            webWorker.workerSetupComplete();
        }
    };
});