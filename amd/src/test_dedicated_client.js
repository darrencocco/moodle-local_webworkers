define(['local_webworkers/web_worker'], function(webWorker) {
    return {
        init: function(incomingTextMessageFunction) {
            let dedicatedWorker = new Worker(webWorker.getURI("local_webworkerstest/test_dedicated_worker"));
            dedicatedWorker.addEventListener("message", function(event) {
                switch (event.data?.type) {
                    case 'TextMessage':
                        incomingTextMessageFunction(event.data);
                        break;
                    default:
                        break;
                }
            });
            dedicatedWorker.postMessage({
                type: 'TextMessage',
                contentsString: 'I have for you a modest proposal.',
            });
        }
    };
});