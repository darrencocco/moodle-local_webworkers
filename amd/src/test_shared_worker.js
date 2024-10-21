define(['local_webworkers/web_worker'], function(webWorker) {
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

            let handleClientMessage = (clientId) => {
                return (e) => {
                    return handleMessage(clientId, e);
                };
            };
            webWorker.workerSetupComplete();
        }
    };
});