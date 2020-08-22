<?php

    // Namespace overhead
    namespace TurtlePHP;

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
                    $msg = '*' . ($dependentDirectory) . '* is required';
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
                    $msg = '*' . ($dependentFile) . '* is required';
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
         * _getErrorLogMessage
         * 
         * @access  protected
         * @static
         * @param   \Throwable $throwable
         * @return  string
         */
        protected static function _getErrorLogMessage(\Throwable $throwable): string
        {
            $logPath = \TurtlePHP\Application::getErrorLogPath();
            $vars = compact('throwable');
            $msg = \TurtlePHP\Application::renderPath($logPath, $vars);
            return $msg;
        }

        /**
         * _getRequestURI
         * 
         * @access  protected
         * @static
         * @return  null|string
         */
        protected static function _getRequestURI(): ?string
        {
            $uri = $_SERVER['REQUEST_URI'] ?? static::getCLIArgument('uri') ?? null;
            return $uri;
        }

        /**
         * _getThrowableTrace
         * 
         * @note    a trace frame is not added to \ErrorException objects since
         *          those are proxies for errors, and doing so causes it to
         *          reference the incorrect method/function name:
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
            $request = \TurtlePHP\Application::getRequest();
            $request->route();
            $request->process();
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
         * _setActiveRecordAutoloader
         * 
         * @access  protected
         * @static
         * @return  void
         */
        protected static function _setActiveRecordAutoloader(): void
        {
            $className = 'TurtlePHP\\Application';
            $methodName = 'handleActiveRecordAutoload';
            $callback = array($className, $methodName);
            \TurtlePHP\Application::addAutoloadClosure($callback);
        }

        /**
         * _setAutoloaders
         * 
         * @access  protected
         * @static
         * @return  void
         */
        protected static function _setAutoloaders(): void
        {
            static::_setActiveRecordAutoloader();
            static::_setControllerAutoloader();
            static::_setModelAutoloader();
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
            if (static::getCLIArgument('CLI') !== null) {
                $cli = true;
            }
            define('CLI', $cli);
        }

        /**
         * _setControllerAutoloader
         * 
         * @access  protected
         * @static
         * @return  void
         */
        protected static function _setControllerAutoloader(): void
        {
            $className = 'TurtlePHP\\Application';
            $methodName = 'handleControllerAutoload';
            $callback = array($className, $methodName);
            \TurtlePHP\Application::addAutoloadClosure($callback);
        }

        /**
         * _setErrorDrawHook
         * 
         * @access  protected
         * @static
         * @return  void
         */
        protected static function _setErrorDrawHook(): void
        {
            $hookKey = 'error/draw';
            $className = '\\TurtlePHP\\Loader';
            $methodName = 'handleErrorDrawHook';
            $callback = array($className, $methodName);
            \TurtlePHP\Application::addHook($hookKey, $callback);
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
            $className = '\\TurtlePHP\\Loader';
            $methodName = 'handleError';
            $callback = array($className, $methodName);
            set_error_handler($callback);
        }

        /**
         * _setErrorHooks
         * 
         * @access  protected
         * @static
         * @return  void
         */
        protected static function _setErrorHooks(): void
        {
            static::_setErrorDrawHook();
            static::_setErrorLogHook();
        }

        /**
         * _setErrorLogHook
         * 
         * @access  protected
         * @static
         * @return  void
         */
        protected static function _setErrorLogHook(): void
        {
            $hookKey = 'error/log';
            $className = '\\TurtlePHP\\Loader';
            $methodName = 'handleErrorLogHook';
            $callback = array($className, $methodName);
            \TurtlePHP\Application::addHook($hookKey, $callback);
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
            $className = '\\TurtlePHP\\Loader';
            $methodName = 'handleException';
            $callback = array($className, $methodName);
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
         * _setModelAutoloader
         * 
         * @access  protected
         * @static
         * @return  void
         */
        protected static function _setModelAutoloader(): void
        {
            $className = 'TurtlePHP\\Application';
            $methodName = 'handleModelAutoload';
            $callback = array($className, $methodName);
            \TurtlePHP\Application::addAutoloadClosure($callback);
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
            $className = '\\TurtlePHP\\Loader';
            $methodName = 'handleShutdown';
            $callback = array($className, $methodName);
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
            $request = new \TurtlePHP\Request($uri);
            \TurtlePHP\Application::setRequest($request);
        }

        /**
         * getCLIArgument
         * 
         * @access  public
         * @static
         * @param   string $key
         * @return  null|string
         */
        public static function getCLIArgument(string $key): ?string
        {
            $argv = $_SERVER['argv'] ?? array();
            foreach ($argv as $value) {
                if (strstr($value, '=') === false) {
                    continue;
                }
                $pieces = explode('=', $value);
                if ($key === $pieces[0]) {
                    return $pieces[1];
                }
            }
            return null;
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
         * handleErrorDrawHook
         * 
         * @access  public
         * @static
         * @param   \TurtlePHP\Request $request
         * @param   \Throwable $throwable
         * @param   array $trace
         * @return  void
         */
        public static function handleErrorDrawHook(\TurtlePHP\Request $request, \Throwable $throwable, array $trace): void
        {
            $errorViewPath = \TurtlePHP\Application::getErrorViewPath();
            $vars = compact('request', 'throwable', 'trace');
            $args = array($errorViewPath, $vars);
            $response = \TurtlePHP\Application::renderPath(... $args);
            $request->setResponse($response);
            $request->setServiceUnavailableHeaders();
        }

        /**
         * handleErrorLogHook
         * 
         * @access  public
         * @static
         * @param   \TurtlePHP\Request $request
         * @param   \Throwable $throwable
         * @param   array $trace
         * @return  void
         */
        public static function handleErrorLogHook(\TurtlePHP\Request $request, \Throwable $throwable, array $trace): void
        {
            $msg = static::_getErrorLogMessage($throwable);
            error_log($msg);
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
            $trace = static::_getThrowableTrace($throwable);
            static::_clearPossibleOutputBuffer();
            $request = \TurtlePHP\Application::getRequest();
            $args = array($request, $throwable, $trace);
            \TurtlePHP\Application::triggerHooks('error/custom', $args);
            \TurtlePHP\Application::triggerHooks('error/draw', $args);
            \TurtlePHP\Application::triggerHooks('error/log', $args);
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
            $request = \TurtlePHP\Application::getRequest();
            $response = $request->getResponse();
            $args = array($response);
            \TurtlePHP\Application::triggerHooks('shutdown', $args);
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
            static::_setErrorHooks();
            static::_setAutoloaders();
            static::_setupRequest();
            static::_loadConfigFiles();
            static::_handleRequest();
            exit(0);
        }
    }
