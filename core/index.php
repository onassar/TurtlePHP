<?php

    /**
     * Constants
     */

    // request start time
    define('START', microtime(true));

    // path constants
    define('APP', ($_SERVER['DOCUMENT_ROOT']) . '/application');
    define('CORE', ($_SERVER['DOCUMENT_ROOT']) . '/core');

    // ip
    $ip = $_SERVER['REMOTE_ADDR'];
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    }
    define('IP', $ip);

    /**
     * shutdown function. Handles buffer-flushing and error-handling.
     * 
     * @access public
     * @return void
     */
    function shutdown()
    {
        // error check
        $error = error_get_last();

        // clean request
        if (empty($error)) {
            $response = \Turtle\Request::getResponse();
            echo $response;
            exit(0);
        }

        // error include
        require_once \Turtle\Request::getErrorPath();
        exit(0);
    }

    // buffer-flushing and error/exception handling
    register_shutdown_function('shutdown');

    // dependencies
    require_once CORE . '/Controller.class.php';
    require_once CORE . '/Model.class.php';
    require_once CORE . '/Request.class.php';

    /**
     * closure function.
     * 
     * @note purpose is to preserve the global namespace from becoming polluted
     * @access public
     * @return void
     */
    $closure = function()
    {
        // application checking
        require_once CORE . '/includes/checks.inc.php';

        // application setup
        require_once APP . '/routes.inc.php';
        require_once APP . '/init.inc.php';

        // route and serve the request
        require_once CORE . '/includes/route.inc.php';
        require_once CORE . '/includes/serve.inc.php';
    };
    $closure();
    unset($closure);
    exit(0);
