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
     * Handles buffer-flushing.
     *
     * @note   when this function is called during an error-flow (eg. <proxy>
     *         below has been called), it will not error itself; it will simply
     *         echo out a <NULL> variable
     * @note   <exit> is not required here (as it doesn't actually do
     *         anything), but I include it for syntactical-sake.
     * @access public
     * @return void
     */
    function shutdown()
    {
        // grab the request
        $request = \Turtle\Application::getRequest();

        // dump the generated response
        echo $request->getResponse();
        exit(0);
    }

    // buffer-flushing and error/exception handling
    register_shutdown_function('shutdown');

    /**
     * proxy
     * 
     * Proxies any errors through the error-hook.
     * 
     * @access public
     * @param  Integer $errno
     * @param  String $errostr
     * @param  String $errfile
     * @param  Integer $errline
     * @param  Array $errcontext
     * @return void
     */
    function proxy($errno, $errstr, $errfile, $errline, $errcontext)
    {
        // grab the request and error
        $request = \Turtle\Application::getRequest();
        $error = func_get_args();

        // route through the error-hook
        $hook = \Turtle\Application::getHook('error');
        call_user_func_array($hook, array($request, $error));
        exit(0);
    }
    set_error_handler('proxy');

    // dependencies
    require_once CORE . '/Application.class.php';
    require_once CORE . '/Controller.class.php';
    require_once CORE . '/Model.class.php';
    require_once CORE . '/Request.class.php';

    // setup error hook
    \Turtle\Application::addHook(
        'error',

        /**
         * (anonymous)
         *
         * @access private
         * @param  Request $request
         * @param  Array $error
         * @return void
         */
        function(\Turtle\Request $request, array $error) {

            // basic error-log
            error_log(
                $error[1] . ' in ' .
                $error[2] . ': ' .
                $error[3]
            );

            // standard-error flow
            require_once $request->getErrorPath();
        }
    );

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
         * @access protected
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
