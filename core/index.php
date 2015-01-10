<?php

    /**
     * Constants
     *
     */

    // request start time
    define('START', microtime(true));

    // path constants
    define('APP', ($_SERVER['DOCUMENT_ROOT']) . '/application');
    define('WEBROOT', (APP) . '/webroot');
    define('CORE', ($_SERVER['DOCUMENT_ROOT']) . '/core');

    // ip
    $ip = $_SERVER['REMOTE_ADDR'];
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    }
    define('IP', $ip);

    // https check
    $https = false;
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
        $https = true;
    }
    if (
        isset($_SERVER['HTTP_X_FORWARDED_PROTO'])
        && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https'
    ) {
        $https = true;
    }
    define('HTTPS', $https);

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
     * Clears out the buffer first; reasons are documented below.
     * 
     * @access public
     * @param  Exception|Integer $errno
     * @param  string $errostr (optional)
     * @param  string $errfile (optional)
     * @param  integer $errline (optional)
     * @param  array $errcontext (optional)
     * @return void
     */
    function proxy()
    {
        // determine if it was an exception that was trigged; set accordingly
        $error = func_get_args();
        if (is_object($error[0])) {

            // breakdown the error arguments
            $exception = $error[0];
            $error = array(
                $exception->getCode(),
                $exception->getMessage(),
                $exception->getFile(),
                $exception->getLine(),
                array()
            );
            $trace = $exception->getTrace();
            array_unshift($trace, array(
                'file' => $exception->getFile(),
                'line' => $exception->getline()
            ));
        } else {
            $trace = debug_backtrace();
        }

        /**
         * Clear the buffer; this ensures that if an error happened in an
         * included file, the contents of the file up until the
         * erroring-line won't be output to the buffer.
         */
        ob_end_clean();

        // grab the request
        $request = \Turtle\Application::getRequest();

        // route through the error-hooks
        $hooks = \Turtle\Application::getHooks('error');
        foreach ($hooks as $hook) {
            call_user_func_array($hook, array($request, $error, $trace));
        }
        exit(0);
    }
    set_error_handler('proxy');
    set_exception_handler('proxy');

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
         * @param  array $error
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
            $closure = function() use ($request) {
                require_once $request->getErrorPath();
            };
            $closure();
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

    // call closure, cleanup, exit (triggering <shutdown> function)
    $closure();
    unset($closure);
    exit(0);
