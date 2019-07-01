Adds a way of using [Web Workers][1] inside of YUI and AMD code

Web workers give you the ability to run JavaScript code in a thread
that will not interrupt the main thread that all the DOM interactions
etc run on.

For YUI use:
```
var myWorkerName = 
  new Worker('/local/webworkers/loader.php/-1/{plugin}/{component}');
myWorkername.onmessage = {bind function here};
```

For AMD use
```
define([
  'local_webworkers/loader!{plugin}/{component}'
], function({myWorkerName}) {
   myWorkerName.onmessage = {bind function here};
});
```



[1]: https://developer.mozilla.org/en-US/docs/Web/API/Web_Workers_API/Using_web_workers