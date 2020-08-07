<?php

    // framework namespace
    namespace Turtle;

    /**
     * Loader
     * 
     * @abstract
     */
    abstract class Loader
    {
        /**
         * _initiated
         *
         * @access  protected
         * @var     bool (default: false)
         * @static
         */
        protected static $_initiated = false;

        /**
         * _checkDependencies
         * 
         * @access  protected
         * @static
         * @return  void
         */
        protected static function _checkDependencies(): void
        {
            static::_checkDependentDirectories();
            static::_checkDependentFiles();
        }

        /**
         * _checkDependentDirectories
         * 
         * @throws  \Exception
         * @access  protected
         * @static
         * @return  void
         */
        protected static function _checkDependentDirectories(): void
        {
            $dependentDirectories = array();
            array_push($dependentDirectories, APP . '/controllers');
            array_push($dependentDirectories, APP . '/webroot');
            foreach ($dependentDirectories as $dependentDirectory) {
                if (is_dir($dependentDirectory) === false) {
                    $msg = '*' . ($dependentDirectory) . '* is required.';
                    throw new \Exception($msg);
                }
            }
        }

        /**
         * _checkDependentFiles
         * 
         * @throws  \Exception
         * @access  protected
         * @static
         * @return  void
         */
        protected static function _checkDependentFiles(): void
        {
            $dependentFiles = array();
            array_push($dependentFiles, APP . '/init.inc.php');
            array_push($dependentFiles, APP . '/routes.inc.php');
            foreach ($dependentFiles as $dependentFile) {
                if (file_exists($dependentFile) === false) {
                    $msg = '*' . ($dependentFile) . '* is required.';
                    throw new \Exception($msg);
                }
            }
        }

        /**
         * _clearPossibleOutputBuffer
         * 
         * Clear the buffer; this ensures that if an error happened in an
         * included file, the contents of the file up until the erroring-line
         * won't be output to the buffer.
         * 
         * @access  protected
         * @static
         * @return  void
         */
        protected static function _clearPossibleOutputBuffer(): void
        {
            ob_end_clean();
        }

        /**
         * _getRequestURI
         * 
         * @access  protected
         * @static
         * @return  string
         */
        protected static function _getRequestURI(): string
        {
            $uri = $_SERVER['REQUEST_URI'] ?? static::getCLIArgument('uri');
            return $uri;
        }

        /**
         * _getThrowableMetadata
         * 
         * @access  protected
         * @static
         * @param   \Throwable $throwable
         * @return  array
         */
        protected static function _getThrowableMetadata(\Throwable $throwable): array
        {
            $errno = $throwable->getCode();
            $errstr = $throwable->getMessage();
            $errfile = $throwable->getFile();
            $errline = $throwable->getLine();
            $errcontext = array();
            $metadata = array($errno, $errstr, $errfile, $errline, $errcontext);
            return $metadata;
        }

        /**
         * _getThrowableTrace
         * 
         * @note    a trace frame is not added to \ErrorException objects since
         *          those are proxies for errors, and doing so causes the
         *          to reference the error handler incorrectly:
         *          - https://i.imgur.com/9LhwFPb.png
         * @access  protected
         * @static
         * @param   \Throwable $throwable
         * @return  array
         */
        protected static function _getThrowableTrace(\Throwable $throwable): array
        {
            $trace = $throwable->getTrace();
            if ($throwable instanceof \ErrorException) {
                return $trace;
            }
            $file = $throwable->getFile();
            $line = $throwable->getLine();
            $traceFrame = compact('file', 'line');
            array_unshift($trace, $traceFrame);
            return $trace;
        }

        /**
         * _handleRequest
         * 
         * @access  protected
         * @static
         * @return  void
         */
        protected static function _handleRequest(): void
        {
            $request = \Turtle\Application::getRequest();
            $request->route();
            $request->generate();
        }

        /**
         * _loadConfigFiles
         * 
         * @access  protected
         * @static
         * @return  void
         */
        protected static function _loadConfigFiles(): void
        {
            require_once APP . '/routes.inc.php';
            require_once APP . '/init.inc.php';
        }

        /**
         * _loadDependencies
         * 
         * @access  protected
         * @static
         * @return  void
         */
        protected static function _loadDependencies(): void
        {
            require_once 'Application.class.php';
            require_once 'Base.class.php';
            require_once 'ActiveRecord.class.php';
            require_once 'Controller.class.php';
            require_once 'Model.class.php';
            require_once 'Request.class.php';
        }

        /**
         * _setActiveRecordAutoLoader
         * 
         * @access  protected
         * @static
         * @return  void
         */
        protected static function _setActiveRecordAutoLoader(): void
        {
            $callback = array('Turtle\\Application', 'activeRecordAutoloader');
            \Turtle\Application::addAutoloadClosure($callback);
        }

        /**
         * _setAutoLoaders
         * 
         * @access  protected
         * @static
         * @return  void
         */
        protected static function _setAutoLoaders(): void
        {
            static::_setActiveRecordAutoLoader();
            static::_setModelAutoLoader();
        }

        /**
         * _setCLI
         * 
         * @access  protected
         * @static
         * @return  void
         */
        protected static function _setCLI(): void
        {
            $cli = false;
            if (static::getCLIArgument('CLI') !== false) {
                $cli = true;
            }
            define('CLI', $cli);
        }

        /**
         * _setErrorHandler
         * 
         * @access  protected
         * @static
         * @return  void
         */
        protected static function _setErrorHandler(): void
        {
            $callback = array('\\Turtle\\Loader', 'handleError');
            set_error_handler($callback);
        }

        /**
         * _setErrorHook
         * 
         * @access  protected
         * @static
         * @return  void
         */
        protected static function _setErrorHook(): void
        {
            $hookKey = 'error';
            $callback = array('\\Turtle\\Loader', 'handleErrorHook');
            \Turtle\Application::addHook($hookKey, $callback);
        }

        /**
         * _setExceptionHandler
         * 
         * @access  protected
         * @static
         * @return  void
         */
        protected static function _setExceptionHandler(): void
        {
            $callback = array('\\Turtle\\Loader', 'handleException');
            set_exception_handler($callback);
        }

        /**
         * _setHTTPS
         * 
         * @access  protected
         * @static
         * @return  void
         */
        protected static function _setHTTPS(): void
        {
            $https = false;
            if (isset($_SERVER['HTTPS']) === true) {
                $https = $_SERVER['HTTPS'] === 'on' ?? $https;
            }
            if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) === true) {
                $https = $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https' ?? $https;
            }
            define('HTTPS', $https);
        }

        /**
         * _setInitiated
         * 
         * @access  protected
         * @static
         * @return  void
         */
        protected static function _setInitiated(): void
        {
            static::$_initiated = true;
        }

        /**
         * _setModelAutoLoader
         * 
         * @access  protected
         * @static
         * @return  void
         */
        protected static function _setModelAutoLoader(): void
        {
            $callback = array('Turtle\\Application', 'modelAutoloader');
            \Turtle\Application::addAutoloadClosure($callback);
        }

        /**
         * _setPathConstants
         * 
         * @access  protected
         * @static
         * @return  void
         */
        protected static function _setPathConstants(): void
        {
            $root = $_SERVER['DOCUMENT_ROOT'] ?? '';
            if ($root === '') {
                $root = $_SERVER['PWD'];
                $root = ($root) . '/TurtlePHP';
            }
            define('ROOT', $root);
            define('APP', ($root) . '/application');
            define('WEBROOT', (APP) . '/webroot');
            define('CORE', ($root) . '/core');
        }

        /**
         * _setRequestIPAddress
         * 
         * @access  protected
         * @static
         * @return  void
         */
        protected static function _setRequestIPAddress(): void
        {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? false;
            define('IP', $ip);
        }

        /**
         * _setShutdownHandler
         * 
         * @access  protected
         * @static
         * @return  void
         */
        protected static function _setShutdownHandler(): void
        {
            $callback = array('\\Turtle\\Loader', 'handleShutdown');
            register_shutdown_function($callback);
        }

        /**
         * _setupRequest
         * 
         * @access  protected
         * @static
         * @return  void
         */
        protected static function _setupRequest(): void
        {
            $uri = static::_getRequestURI();
            $request = new \Turtle\Request($uri);
            \Turtle\Application::setRequest($request);
        }

        /**
         * getCLIArgument
         * 
         * @access  public
         * @static
         * @param   string $key
         * @return  mixed
         */
        public static function getCLIArgument(string $key)
        {
            $hash = array();
            if (isset($_SERVER['argv']) === true) {
                foreach ($_SERVER['argv'] as $value) {
                    if (strstr($value, '=') !== false) {
                        $pieces = explode('=', $value);
                        $hash[$pieces[0]] = $pieces[1];
                    }
                }
            }
            if (isset($hash[$key]) === true) {
                return $hash[$key];
            }
            return false;
        }

        /**
         * handleError
         * 
         * @link    https://www.php.net/manual/en/function.set-error-handler.php
         * @access  public
         * @static
         * @param   int $errno
         * @param   string $errstr
         * @param   string $errfile
         * @param   string $errline
         * @param   array $errcontext
         * @return  void
         */
        public static function handleError(int $errno, string $errstr, string $errfile, string $errline, array $errcontext): void
        {
            $args = array($errstr, $errno, E_ERROR, $errfile, $errline);
            $errorException = new \ErrorException(... $args);
            static::handleException($errorException);
        }

        /**
         * handleErrorHook
         * 
         * @access  public
         * @static
         * @param   \Turtle\Request $request
         * @param   array $metadata
         * @param   array $trace
         * @return  void
         */
        public static function handleErrorHook(\Turtle\Request $request, array $metadata, array $trace): void
        {
            $errstr = $metadata[1];
            $errfile = $metadata[2];
            $errline = $metadata[3];
            $msg = ($errstr) . ' in ' . ($errfile) . ': ' . ($errline);
            error_log($msg);
            $errorPath = $request->getErrorPath();
            $closure = function() use ($errorPath): void {
                require_once $errorPath;
            };
            call_user_func($closure);
        }

        /**
         * handleException
         * 
         * @link    https://www.php.net/manual/en/function.set-exception-handler.php
         * @access  public
         * @static
         * @param   \Throwable $throwable
         * @return  void
         */
        public static function handleException(\Throwable $throwable): void
        {
            $metadata = static::_getThrowableMetadata($throwable);
            $trace = static::_getThrowableTrace($throwable);
            static::_clearPossibleOutputBuffer();
            $request = \Turtle\Application::getRequest();
            $args = array($request, $metadata, $trace);
            \Turtle\Application::triggerHooks('error', $args);
            exit(0);
        }

        /**
         * handleShutdown
         * 
         * @access  public
         * @static
         * @return  void
         */
        public static function handleShutdown(): void
        {
            $request = \Turtle\Application::getRequest();
            $response = $request->getResponse();
            $args = array($response);
            \Turtle\Application::triggerHooks('flush', $args);
            $response = $request->getResponse();
            echo $response;
            exit(0);
        }

        /**
         * init
         * 
         * @note    Ordered
         * @access  public
         * @static
         * @return  bool
         */
        public static function init(): bool
        {
            if (static::$_initiated === true) {
                return false;
            }
            static::_loadDependencies();
            static::_setCLI();
            static::_setHTTPS();
            static::_setPathConstants();
            static::_checkDependencies();
            static::_setRequestIPAddress();
            static::_setErrorHandler();
            static::_setExceptionHandler();
            static::_setShutdownHandler();
            static::_setErrorHook();
            static::_setAutoLoaders();
            static::_setupRequest();
            static::_loadConfigFiles();
            static::_handleRequest();
            exit(0);
        }
    }
