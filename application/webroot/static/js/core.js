js(
    [
        'https://ajax.googleapis.com/ajax/libs/mootools/1.4.1/mootools-yui-compressed.js'
    ],
    function() {
        log('pre: ', (new Date()).getTime() - start);
        queue.process();
        log('post: ', (new Date()).getTime() - start);
    }
);
