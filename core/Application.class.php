<?php

    // Namespace overhead
    namespace TurtlePHP;

    /**
     * Application
     * 
     * @abstract
     */
    abstract class Application
    {
        /**
         * _404LogPath
         * 
         * @access  protected
         * @var     string (default: './logs/404.inc.php')
         * @static
         */
        protected static $_404LogPath = './logs/404.inc.php';

        /**
         * _errorLogPath
         * 
         * @access  protected
         * @var     string (default: './logs/error.inc.php')
         * @static
         */
        protected static $_errorLogPath = './logs/error.inc.php';

        /**
         * _errorViewPath
         * 
         * @access  protected
         * @var     string (default: './views/error.inc.php')
         * @static
         */
        protected static $_errorViewPath = './views/error.inc.php';

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
         * _routeEvaluator
         * 
         * @access  protected
         * @var     null|callable (default: null)
         * @static
         */
        protected static $_routeEvaluator = null;

        /**
         * _routes
         * 
         * @access  protected
         * @var     array (default: array())
         * @static
         */
        protected static $_routes = array();

        /**
         * _getDirectories
         * 
         * @access  protected
         * @static
         * @param   string $path
         * @return  array
         */
        protected static function _getDirectories(string $path): array
        {
            $directories = glob($path . '/*' , GLOB_ONLYDIR);
            return $directories;
        }

        /**
         * _getNormalizedRoutes
         * 
         * @access  protected
         * @static
         * @param   array $routes
         * @return  array
         */
        protected static function _getNormalizedRoutes(array $routes): array
        {
            foreach ($routes as $path => &$route) {

                // Redirect matching
                if (is_string($route) === true) {
                    $route = array(
                        'redirect' => $route
                    );
                }

                // Path normalization
                $route['path'] = $path;
            }
            $routes = array_values($routes);
            return $routes;
        }

        /**
         * _includeAlternativeRoutes
         * 
         * @access  protected
         * @static
         * @param   array $routes
         * @return  array
         */
        protected static function _includeAlternativeRoutes(array $routes): array
        {
            foreach ($routes as $path => $route) {
                $alternatives = $route['alternatives'] ?? array();
                $alternatives = (array) $alternatives;
                if (empty($alternatives) === true) {
                    continue;
                }
                foreach ($alternatives as $alternative) {
                    $alternativeRoute = $route;
                    unset($alternativeRoute['alternatives']);
                    $routes[$alternative] = $alternativeRoute;
                }
            }
            return $routes;
        }

        /**
         * addAutoloadClosure
         * 
         * @link    https://www.php.net/manual/en/function.spl-autoload-register.php
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
            static::$_hooks[$hookKey] = static::$_hooks[$hookKey] ?? array();
            array_push(static::$_hooks[$hookKey], $callback);
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
            array_push(static::$_requests, $request);
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
            $route['path'] = $path;
            array_unshift(static::$_routes, $route);
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
            $routes = static::_includeAlternativeRoutes($routes);
            $normalizedRoutes = static::_getNormalizedRoutes($routes);
            static::$_routes = array_merge($normalizedRoutes, static::$_routes);
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
            static::$_hooks[$hookKey] = array();
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
            static::$_routes = array();
        }

        /**
         * get404LogPath
         * 
         * @access  public
         * @static
         * @return  string
         */
        public static function get404LogPath(): string
        {
            $path = static::$_404LogPath;
            return $path;
        }

        /**
         * getErrorLogPath
         * 
         * @access  public
         * @static
         * @return  string
         */
        public static function getErrorLogPath(): string
        {
            $errorLogPath = static::$_errorLogPath;
            return $errorLogPath;
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
            $errorViewPath = static::$_errorViewPath;
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
            $hooks = static::$_hooks[$hookKey] ?? array();
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
            $request->process();
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
            $request = static::$_request;
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
            $requests = static::$_requests;
            return $requests;
        }

        /**
         * getRouteEvaluator
         * 
         * Returns the callable, if any, that ought to be used when determining
         * whether a route can be matched.
         * 
         * @access  public
         * @static
         * @return  null|callable
         */
        public static function getRouteEvaluator(): ?callable
        {
            $evaluator = static::$_routeEvaluator;
            return $evaluator;
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
        public static function getRoutes(): array
        {
            $routes = static::$_routes;
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
                list(, $filename) = explode('\\', $className);
                $basename = ($filename) . '.class.php';
                $path = APP . '/activeRecords/' . ($basename);
                if (substr_count($className, '\\') === 2) {
                    list(, $directory, $filename) = explode('\\', $className);
                    // $directory = strtolower($directory);
                    $directory = lcfirst($directory);
                    $basename = ($filename) . '.class.php';
                    $path = APP . '/activeRecords/' . ($directory) . '/' . ($basename);
                }
                require_once $path;
            }
        }

        /**
         * handleCollectionAutoload
         * 
         * @access  public
         * @static
         * @param   string $className
         * @return  void
         */
        public static function handleCollectionAutoload(string $className): void
        {
            if (preg_match('/^Collection\\\/', $className) === 1) {
                list(, $filename) = explode('\\', $className);
                $basename = ($filename) . '.class.php';
                $path = APP . '/collections/' . ($basename);
                if (substr_count($className, '\\') === 2) {
                    list(, $directory, $filename) = explode('\\', $className);
                    // $directory = strtolower($directory);
                    $directory = lcfirst($directory);
                    $basename = ($filename) . '.class.php';
                    $path = APP . '/collections/' . ($directory) . '/' . ($basename);
                }
                require_once $path;
            }
        }

        /**
         * handleControllerAutoload
         * 
         * @access  public
         * @static
         * @param   string $className
         * @return  void
         */
        public static function handleControllerAutoload(string $className): void
        {
            if (preg_match('/^Controller\\\/', $className) === 1) {
                list(, $filename) = explode('\\', $className);
                $basename = ($filename) . '.class.php';
                $path = APP . '/controllers/' . ($basename);
                if (substr_count($className, '\\') === 2) {
                    list(, $directory, $filename) = explode('\\', $className);
                    // $directory = strtolower($directory);
                    $directory = lcfirst($directory);
                    $basename = ($filename) . '.class.php';
                    $path = APP . '/controllers/' . ($directory) . '/' . ($basename);
                }
                require_once $path;
            }
        }

        /**
         * handleHelperAutoload
         * 
         * @access  public
         * @static
         * @param   string $className
         * @return  void
         */
        public static function handleHelperAutoload(string $className): void
        {
            if (preg_match('/^Helper\\\/', $className) === 1) {
                list(, $filename) = explode('\\', $className);
                $basename = ($filename) . '.class.php';
                $path = APP . '/helpers/' . ($basename);
                if (substr_count($className, '\\') === 2) {
                    list(, $directory, $filename) = explode('\\', $className);
                    // $directory = strtolower($directory);
                    $directory = lcfirst($directory);
                    $basename = ($filename) . '.class.php';
                    $path = APP . '/helpers/' . ($directory) . '/' . ($basename);
                }
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
                list(, $filename) = explode('\\', $className);
                $basename = ($filename) . '.class.php';
                $path = APP . '/models/' . ($basename);
                if (substr_count($className, '\\') === 2) {
                    list(, $directory, $filename) = explode('\\', $className);
                    // $directory = strtolower($directory);
                    $directory = lcfirst($directory);
                    $basename = ($filename) . '.class.php';
                    $path = APP . '/models/' . ($directory) . '/' . ($basename);
                }
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
            require $_path;
        }

        /**
         * renderPath
         * 
         * @link    https://www.php.net/manual/en/function.ob-get-clean.php
         * @access  public
         * @static
         * @param   string $_path
         * @param   array $_vars (default: array())
         * @return  string
         */
        public static function renderPath(string $_path, array $_vars = array()): string
        {
            ob_start();
            static::loadPath($_path, $_vars);
            $response = ob_get_clean();
            return $response;
        }

        /**
         * set404LogPath
         * 
         * @access  public
         * @static
         * @param   string $path
         * @return  void
         */
        public static function set404LogPath(string $path): void
        {
            static::$_404LogPath = $path;
        }

        /**
         * setErrorLogPath
         * 
         * @access  public
         * @static
         * @param   string $errorLogPath
         * @return  void
         */
        public static function setErrorLogPath(string $errorLogPath): void
        {
            static::$_errorLogPath = $errorLogPath;
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
            static::$_errorViewPath = $errorViewPath;
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
            static::$_request = $request;
        }

        /**
         * setRouteEvaluator
         * 
         * Sets the callable evaluator that should be used during route
         * matching. This can be helpful for running more than one domain within
         * the codebase.
         * 
         * @access  public
         * @static
         * @param   callable $evaluator
         * @return  void
         */
        public static function setRouteEvaluator(callable $evaluator): void
        {
            static::$_routeEvaluator = $evaluator;
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
            $normalizedRoutes = static::_getNormalizedRoutes($routes);
            static::$_routes = $normalizedRoutes;
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
            foreach ($hooks as $callback) {
                call_user_func_array($callback, $args);
            }
        }
    }
