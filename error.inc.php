<?php

    // error headers
    header(
        ($_SERVER['SERVER_PROTOCOL']) . ' 503 Service Temporarily Unavailable'
    );
    header('Status: 503 Service Temporarily Unavailable');
    header('Retry-After: 7200');
    header('Content-Type: text/html; charset=utf-8');

?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <title>Whoops.</title>
        <meta name="description" content="" />
        <meta name="author" content="" />


        <!-- bootstrap -->
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <link href="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css" rel="stylesheet" />
        <style type="text/css">
            body {
                background-image: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAB4AAADkCAIAAAAAWwOKAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAA2ZpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuMC1jMDYwIDYxLjEzNDc3NywgMjAxMC8wMi8xMi0xNzozMjowMCAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wTU09Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9tbS8iIHhtbG5zOnN0UmVmPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvc1R5cGUvUmVzb3VyY2VSZWYjIiB4bWxuczp4bXA9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC8iIHhtcE1NOk9yaWdpbmFsRG9jdW1lbnRJRD0ieG1wLmRpZDpGNzdGMTE3NDA3MjA2ODExOEVENEVFQUJGQTZGRjEyQyIgeG1wTU06RG9jdW1lbnRJRD0ieG1wLmRpZDpDNzcyNDNEQTEwOUYxMUUxQkM3NThDOUVFM0ZCQ0EyMyIgeG1wTU06SW5zdGFuY2VJRD0ieG1wLmlpZDpDNzcyNDNEOTEwOUYxMUUxQkM3NThDOUVFM0ZCQ0EyMyIgeG1wOkNyZWF0b3JUb29sPSJBZG9iZSBQaG90b3Nob3AgQ1M1IE1hY2ludG9zaCI+IDx4bXBNTTpEZXJpdmVkRnJvbSBzdFJlZjppbnN0YW5jZUlEPSJ4bXAuaWlkOkY4N0YxMTc0MDcyMDY4MTE4RUQ0RUVBQkZBNkZGMTJDIiBzdFJlZjpkb2N1bWVudElEPSJ4bXAuZGlkOkY3N0YxMTc0MDcyMDY4MTE4RUQ0RUVBQkZBNkZGMTJDIi8+IDwvcmRmOkRlc2NyaXB0aW9uPiA8L3JkZjpSREY+IDwveDp4bXBtZXRhPiA8P3hwYWNrZXQgZW5kPSJyIj8+ff0lOAAAASFJREFUeNrs2EEKgzAQQNHq/e/rTmtBK0rUmExKFy8rIcNnVvqwG4bhlX3Gccwf7lt159Oom7t1QTcrXda9Txd3b9I13at0Zfc0Xd9Np0O6iXRU95gO7O7Ssd0tHd5d0i26n8lG3Wcv1UfdeeO+UTd364JuVrqse58u7t6ka7pX6cruabq+m06HdBPpqO4xHdjdpWO7Wzq8u6RbdJ+xj1RJlVRJlVRJlVRJlVRJlVRJlVRJlVRJlVRJlVRJlVRJlVRJlVRJlVRJlVRJlVRJlVRJlVRJlVRJlVRJlVRJlVRJlVRJlVRJlVRJlVRJlVRJlVRJlVT/QarT2p3Obr/PT6Xarft2ydvdJKmSKqmSKqmSKqmSKqmSavmX0z/V30n1LcAAOb20xXawIikAAAAASUVORK5CYII=);
                padding-top: 40px;
                padding-bottom: 40px;
                background-color: #f5f5f5;
            }
            h1 {
                font-weight: 300;
            }
            a {
                text-decoration: underline;
            }
            #content {
                max-width: 480px;
                padding: 16px 32px 32px;
                margin: 20px auto 0;
                background-color: #fff;
                border: 1px solid #e5e5e5;
                /*border-color: #c6c6c6;*/
                -webkit-border-radius: 8px;
                -moz-border-radius: 8px;
                border-radius: 8px;
                -webkit-box-shadow: 0 1px 2px rgba(0,0,0,.05);
                -moz-box-shadow: 0 1px 2px rgba(0,0,0,.05);
                box-shadow: 0 1px 2px rgba(0,0,0,.05);
            }
            #details {
                max-width: 480px;
                margin: 10px auto 20px;
                font-size: 9px;
                color: #888;
                text-align: left;
            }
            #details span {
                padding-right: 12px;
            }
        </style>
        <!-- shiv -->
        <!--[if lt IE 9]>
        <script src="//html5shim.googlecode.com/svn/trunk/html5.js"></script>
        <![endif]-->
    </head>
    <body>
        <div class="container">
            <div id="content">
                <h1>Whoops!</h1>
                <hr />
                <p>
                    Sorry, but something went wrong.<br />
                    We&#039;re looking into it.<br /><br />
                    In the meantime, try going
                    <a href="Javascript: history.go(-1);" title="Go back one page">back</a>.
                </p>
            </div>
        </div>
    </body>
</html>
