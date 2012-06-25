<?php

    /**
     * Constants
     * 
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
     * shutdown
     * 
     * Handles buffer-flushing and error-handling.
     * 
     * @access public
     * @return void
     */
    function shutdown()
    {
        // grab the request
        $request = \Turtle\Application::getRequest();

        // error check
        $error = error_get_last();

        // clean request
        if (empty($error)) {

            // dump the generated response
            echo $request->getResponse();
            exit(0);
        }

        // error include
        require_once $request->getErrorPath();
        exit(0);
    }

    // buffer-flushing and error/exception handling
    register_shutdown_function('shutdown');

    // dependencies
    require_once CORE . '/Application.class.php';
    require_once CORE . '/Controller.class.php';
    require_once CORE . '/Model.class.php';
    require_once CORE . '/Request.class.php';

    /**
     * closure
     * 
     * Acts as a wrapper to prevent the global namespace from becoming polluted.
     * 
     * @access public
     * @return void
     */
    $closure = function()
    {
        /**
         * checks
         * 
         * Acts as a wrapper to prevent the global namespace from becoming polluted.
         * 
         * @access public
         * @return void
         */
        $checks = function()
        {
            // controllers/webroot directories exist
            if (!is_dir(APP . '/controllers')) {
                throw new Exception(APP . '/controllers doesn\'t exist.');
            } elseif (!is_dir(APP . '/webroot')) {
                throw new Exception(APP . '/webroot doesn\'t exist.');
            }

            // check application init
            if (!file_exists(APP . '/init.inc.php')) {
                throw new Exception(APP . '/init.inc.php doesn\'t exist.');
            }

            // check appliction routes
            if (!file_exists(APP . '/routes.inc.php')) {
                throw new Exception(APP . '/routes.inc.php doesn\'t exist.');
            }
        };

        // call <checks> closure, cleanup
        $checks();
        unset($checks);

        // create request; store as <Application> request
        $request = (new \Turtle\Request($_SERVER['REQUEST_URI']));
        \Turtle\Application::setRequest($request);

        // application setup
        require_once APP . '/routes.inc.php';
        require_once APP . '/init.inc.php';

        // route and generate markup
        $request->route();
        $request->generate();
    };

    // call closure, cleanup, exit (triggering <shutdown> function
    $closure();
    unset($closure);
    exit(0);
