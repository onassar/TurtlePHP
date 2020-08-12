<?php

    // framework namespace
    namespace TurtlePHP;

    /**
     * Application
     * 
     * @abstract
     */
    abstract class Application
    {
        /**
         * _errorViewPath
         * 
         * @access  protected
         * @var     string (default: 'error.inc.php')
         * @static
         */
        protected static $_errorViewPath = 'error.inc.php';

        /**
         * _hooks
         * 
         * @access  protected
         * @var     array (default: array())
         * @static
         */
        protected static $_hooks = array();

        /**
         * _request
         * 
         * Tracks the primary, initiating request, for the application.
         * 
         * @access  protected
         * @var     null|\TurtlePHP\Request (default: null)
         * @static
         */
        protected static $_request = null;

        /**
         * _requests
         * 
         * @access  protected
         * @var     array (default: array())
         * @static
         */
        protected static $_requests = array();

        /**
         * _routes
         * 
         * @access  protected
         * @var     array
         * @static
         */
        protected static $_routes;

        /**
         * addAutoloadClosure
         * 
         * @access  public
         * @static
         * @param   callable $callback
         * @return  void
         */
        public static function addAutoloadClosure(callable $callback): void
        {
            spl_autoload_register($callback);
        }

        /**
         * addHook
         * 
         * @access  public
         * @static
         * @param   string $hookKey
         * @param   callable $callback
         * @return  void
         */
        public static function addHook(string $hookKey, callable $callback): void
        {
            self::$_hooks[$hookKey] = self::$_hooks[$hookKey] ?? array();
            array_push(self::$_hooks[$hookKey], $callback);
        }

        /**
         * addRequest
         * 
         * @access  public
         * @static
         * @param   \TurtlePHP\Request $request
         * @return  void
         */
        public static function addRequest(\TurtlePHP\Request $request): void
        {
            array_push(self::$_requests, $request);
        }

        /**
         * addRoute
         * 
         * Adds a route array to the routes storage. This method allows for
         * flexibility when building plugins. It prepends the route to the
         * routes array to prevent any overarching routes (eg. 404 catch-all
         * redirects) from being matched.
         * 
         * @access  public
         * @static
         * @param   string $path
         * @param   array $route
         * @return  void
         */
        public static function addRoute(string $path, array $route): void
        {
            $route = array_merge($route, array(
                'path' => $path
            ));
            array_unshift(self::$_routes, $route);
        }

        /**
         * addRoutes
         * 
         * The logic here follows the same premise as <addRoute> above; namely
         * to "prepend" the additional routes, collectively, to the beginning of
         * the array of matchable/applicable routes.
         * 
         * <array_merge> rather than <array_unshift> needed to be used here,
         * with the previously-set routes appended to the end, in order for the
         * newly-added routes to have the precendence required of them.
         * 
         * @access  public
         * @static
         * @param   array $routes
         * @return  void
         */
        public static function addRoutes(array $routes): void
        {
            // normalize path-key
            foreach ($routes as $path => &$route) {
                $route['path'] = $path;
            }

            // reindex it (to remove the <path> value as the route's key)
            $routes = array_values($routes);

            // prepend array of routes to possible ones
            self::$_routes = array_merge($routes, self::$_routes);
        }

        /**
         * clearHooks
         * 
         * @access  public
         * @static
         * @param   string $hookKey
         * @return  void
         */
        public static function clearHooks(string $hookKey): void
        {
            self::$_hooks[$hookKey] = array();
        }

        /**
         * clearRoutes
         * 
         * Clears the array of possible routes for the application to match.
         * 
         * @access  public
         * @static
         * @return  void
         */
        public static function clearRoutes(): void
        {
            self::$_routes = array();
        }

        /**
         * getErrorViewPath
         * 
         * @access  public
         * @static
         * @return  string
         */
        public static function getErrorViewPath(): string
        {
            $errorViewPath = self::$_errorViewPath;
            return $errorViewPath;
        }

        /**
         * getHooks
         * 
         * @access  public
         * @static
         * @param   string $hookKey
         * @return  array
         */
        public static function getHooks(string $hookKey): array
        {
            $hooks = self::$_hooks[$hookKey] ?? array();
            return $hooks;
        }

        /**
         * getPath
         * 
         * @access  public
         * @static
         * @param   string $path
         * @return  string
         */
        public static function getPath(string $path): string
        {
            $request = new \TurtlePHP\Request($path);
            $request->route();
            $request->generate();
            $response = $request->getResponse();
            return $response;
        }

        /**
         * getRequest
         * 
         * @access  public
         * @static
         * @return  null|\TurtlePHP\Request
         */
        public static function getRequest(): ?\TurtlePHP\Request
        {
            $request = self::$_request;
            return $request;
        }

        /**
         * getRequests
         * 
         * @access  public
         * @static
         * @return  array
         */
        public static function getRequests(): array
        {
            $requests = self::$_requests;
            return $requests;
        }

        /**
         * getRoutes
         * 
         * Returns the array of all routes the application may accept for a
         * request.
         * 
         * @access  public
         * @static
         * @return  array
         */
        public static function getRoutes()
        {
            $routes = self::$_routes;
            return $routes;
        }

        /**
         * handleActiveRecordAutoload
         * 
         * @access  public
         * @static
         * @param   string $className
         * @return  void
         */
        public static function handleActiveRecordAutoload(string $className): void
        {
            if (preg_match('/^ActiveRecord\\\/', $className) === 1) {
                $basename = preg_replace('/^ActiveRecord\\\/', '', $className);
                $basename = ($basename) . '.class.php';
                $path = APP . '/activeRecords/' . ($basename);
                require_once $path;
            }
        }

        /**
         * handleModelAutoload
         * 
         * @access  public
         * @static
         * @param   string $className
         * @return  void
         */
        public static function handleModelAutoload(string $className): void
        {
            if (preg_match('/^Model\\\/', $className) === 1) {
                $basename = preg_replace('/^Model\\\/', '', $className);
                $basename = ($basename) . '.class.php';
                $path = APP . '/models/' . ($basename);
                require_once $path;
            }
        }

        /**
         * loadPath
         * 
         * @access  public
         * @static
         * @param   string $_path
         * @param   array $_vars (default: array())
         * @return  void
         */
        public static function loadPath(string $_path, array $_vars = array()): void
        {
            foreach ($_vars as $_name => $_value) {
                $$_name = $_value;
            }
            include $_path;
        }

        /**
         * renderPath
         * 
         * @access  public
         * @static
         * @param   string $_path
         * @param   array $_vars (default: array())
         * @return  string
         */
        public static function renderPath(string $_path, array $_vars = array()): string
        {
            foreach ($_vars as $_name => $_value) {
                $$_name = $_value;
            }
            ob_start();
            include $_path;
            $_response = ob_get_contents();
            ob_end_clean();
            return $_response;
        }

        /**
         * setErrorViewPath
         * 
         * @access  public
         * @static
         * @param   string $errorViewPath
         * @return  void
         */
        public static function setErrorViewPath(string $errorViewPath): void
        {
            self::$_errorViewPath = $errorViewPath;
        }

        /**
         * setRequest
         * 
         * @access  public
         * @static
         * @param   \TurtlePHP\Request $request
         * @return  void
         */
        public static function setRequest(\TurtlePHP\Request $request): void
        {
            self::$_request = $request;
        }

        /**
         * setRoutes
         * 
         * Sets an array of all possible routes that the request can match.
         * 
         * @access  public
         * @static
         * @param   array $routes
         * @return  void
         */
        public static function setRoutes(array $routes): void
        {
            // reindex array with path (key) set as attribute
            foreach ($routes as $path => &$route) {
                $route['path'] = $path;
            }
            $routes = array_values($routes);

            // store
            self::$_routes = $routes;
        }

        /**
         * triggerHooks
         * 
         * @access  public
         * @static
         * @param   string $hookKey
         * @param   array $args (default: array())
         * @return  void
         */
        public static function triggerHooks(string $hookKey, array $args = array()): void
        {
            $hooks = static::getHooks($hookKey);
            foreach ($hooks as $hook) {
                call_user_func_array($hook, $args);
            }
        }
    }
