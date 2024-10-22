define(['local_webworkers/web_worker'], function(webWorker) {
    return {
        dedicatedWorker: null,
        init: function(incomingTextMessageFunction) {
            this.dedicatedWorker = new Worker(webWorker.getURI("local_webworkers/test_dedicated_worker"));
            this.dedicatedWorker.addEventListener("message", function(event) {
                switch (event.data?.type) {
                    case 'TextMessage':
                        incomingTextMessageFunction(event.data);
                        break;
                    default:
                        break;
                }
            });
        },
        sendMessage: function() {
            this.dedicatedWorker.postMessage({
                type: 'TextMessage',
                contentsString: 'I have for you a modest proposal.',
            });
        }
    };
});