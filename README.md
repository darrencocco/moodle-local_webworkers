Adds a way of using [Web Workers][1] inside of YUI and AMD code

Web workers give you the ability to run JavaScript code in a thread
that will not interrupt the main thread that all the DOM interactions
etc run on.

For YUI use (deprecated):
```JavaScript
var myWorkerName = 
  new Worker('/local/webworkers/loader.php/-1/{plugin}/{component}');
myWorkername.onmessage = function(event) {
    console.log(event.data);
};
```

For AMD use:
```JavaScript
define(['local_webworkers/worker'], function({worker}) {
    let dedicatedWorker = new Worker(worker.toURI('{plugin}/{component}');
    dedicatedWorker.addEventListener('message', function(event) {
        console.log(event.data);
    });
    dedicatedWorker.postMessage('');
    
    let sharedWorker = new SharedWorker(worker.toURI('{plugin}/{component}');
    sharedWorker.port.addEventListener('message', function(event) {
        console.log(event.data);
    });
    sharedWorker.port.start();
    sharedWorker.port.postMessage('');
});
```

Implement a Dedicated Worker:
```JavaScript
define(['local_webworkers/worker'], function(worker) {
    return {
        init: function() {
            self.addEventListener('message', function(e) {
                self.postMessage({
                    type: 'TextMessage',
                    contentsString: e.data.contentsString,
                });
            });
            worker.workerSetupComplete();
        }
    };
});
```

Implement a Shared Worker:
```JavaScript
define(['local_webworkers/helper'], function(worker) {
    return {
        init: function() {
            let portMap = [];

            /**
             * Generates a sequential set of numbers for the
             * lifetime of the worker.
             * @returns {Generator<number, void, *>}
             */
            function* generatePortNumber() {
                const start = 0;
                const end = Infinity;
                const step = 1;
                for (let i = start; i < end; i += step) {
                    yield i;
                }
            }
            let portNumber =  generatePortNumber();

            self.addEventListener("connect", (e) => {
                let clientId = portNumber.next().value;
                portMap[clientId] = e.ports[0];
                e.ports[0].addEventListener("message", handleClientMessage(clientId));
                e.ports[0].start();
            });

            let handleMessage = (clientId, e) => {
                portMap.forEach(function (port, portId) {
                    if (clientId != portId) {
                        port.postMessage({
                            type: 'TextMessage',
                            clientId: clientId,
                            contentsString: e.data.contentsString,
                        });
                    } else {
                        port.postMessage({
                            type: 'Pong',
                            clientId: clientId,
                            contentsString: e.data.contentsString,
                        });
                    }
                });
            };

            let handleClientMessage = (cliendId) => {
                return (e) => {
                    return handleMessage(cliendId, e);
                };
            };
            worker.workerSetupComplete();
        }
    };
});
```

[1]: https://developer.mozilla.org/en-US/docs/Web/API/Web_Workers_API/Using_web_workers