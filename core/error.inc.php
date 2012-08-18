<?php

    // error headers
    header(
        ($_SERVER['SERVER_PROTOCOL']) . ' 503 Service Temporarily Unavailable'
    );
    header('Status: 503 Service Temporarily Unavailable');
    header('Retry-After: 7200');
    header('Content-Type: text/html; charset=utf-8');

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
    <head>
        <title>Whoops.</title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta name="description" content="Sorry, but something went wrong. We&#039;re looking into it." />
        <link href="http://meyerweb.com/eric/tools/css/reset/reset.css" rel="stylesheet" type="text/css" />
        <link href="http://fonts.googleapis.com/css?family=Nunito:light,regular,bold" rel="stylesheet" type="text/css" />
        <style type="text/css">
            body {
                font-family: "Lucida Grande",verdana,arial,sans-serif;
                color: #666;
                background-color: #eee;
                font-size: 13px;
            }
            a {
                color: #446CBC;
            }
            div.wrapper {
                width: 480px;
                margin: 100px auto 0;
            }
            div.message {
                -webkit-box-shadow: -3px -3px 2px #e9e9e9;
                border-radius: 12px;
            }
            div.message div.container {
                padding: 32px;
                border: 1px solid #d6d6d6;
                border-radius: 12px;
                -webkit-box-shadow: 3px 3px 2px #e9e9e9;
                background-color: #fff;
            }
            div.message div.container h1 {
                font-size: 32px;
                line-height: 31px;
                font-family: Nunito;
                border-bottom: 1px solid #ccc;
                padding: 0 0 16px;
                margin: 0 0 16px;
            }
            div.message div.container p {
                display: block;
                font-size: 13px;
                line-height: 17px;
            }
            div.details {
                font-size: 9px;
                padding: 6px 0 0 33px;
                color: #888;
                text-align: left;
            }
            div.details span {
                padding-right: 12px;
            }
        </style>
    </head>
    <body>
        <div class="wrapper">
            <div class="message">
                <div class="container">
                    <h1>Whoops!</h1>
                    <p>
                        Sorry, but something went wrong.<br />
                        We&#039;re looking into it.<br /><br />
                        In the meantime, try going
                        <a href="Javascript: history.go(-1);" title="Go back one page">back</a>.
                    </p>
                </div>
            </div>
            <div class="details">
                <span><?= date('D, d M o G:i:s T') ?></span>
                <span><?= (IP) ?></span>
                <span><?= ($_SERVER['HTTP_HOST']) ?></span>
            </div>
        </div>
    </body>
</html>
