define(['local_webworkers/web_worker'], function(webWorker) {
    return {
        sharedWorker: null,
        init: function(incomingTextMessageFunction) {
            this.sharedWorker = new SharedWorker(webWorker.getURI("local_webworkers/test_shared_worker"));
            this.sharedWorker.port.addEventListener("message", function(event) {
                switch (event.data?.type) {
                    case 'TextMessage':
                        incomingTextMessageFunction(event.data);
                        break;
                    default:
                        break;
                }
            });
            this.sharedWorker.port.start();
        },

        sendMessage: function() {
            this.sharedWorker.port.postMessage({
                type: 'TextMessage',
                contentsString: 'I have for you a modest proposal.',
            });
        }
    };
});