<?php

    // framework namespace
    namespace Turtle;

    /**
     * Application
     *
     * @abstract
     */
    abstract class Application
    {
        /**
         * _hooks
         *
         * @var    array
         * @access protected
         * @static
         */
        protected static $_hooks = array();

        /**
         * _models
         *
         * @var    array
         * @access protected
         * @static
         */
        protected static $_models = array();

        /**
         * _request
         *
         * @var    Request
         * @access protected
         * @static
         */
        protected static $_request;

        /**
         * _routes
         *
         * @var    array
         * @access protected
         * @static
         */
        protected static $_routes;

        /**
         * addHook
         *
         * @access public
         * @static
         * @param  String $hook
         * @param  mixed $callback Callback array or closure
         * @return void
         */
        public static function addHook($hook, $callback)
        {
            self::$_hooks[$hook] = $callback;
        }

        /**
         * addRoute
         *
         * Adds a route array to the routes storage. This method allows for
         * flexibility when building plugins. It prepends the route to the
         * routes array to prevent any overarching routes (eg. 404 catch-all
         * redirects) from being matched.
         *
         * @access public
         * @static
         * @param  String $path
         * @param  Array $route
         * @return void
         */
        public static function addRoute($path, array $route)
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
         * @access public
         * @static
         * @param  Array $routes
         * @return void
         */
        public static function addRoutes(array $routes)
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
         * clearRoutes
         *
         * Clears the array of possible routes for the application to match.
         *
         * @access public
         * @static
         * @return void
         */
        public static function clearRoutes()
        {
            self::$_routes = array();
        }

        /**
         * getHook
         *
         * @access public
         * @static
         * @param  String $name
         * @return Array
         */
        public static function getHook($name)
        {
            if (isset(self::$_hooks[$name])) {
                return self::$_hooks[$name];
            }
            throw new Exception('Hook *' . ($name) . '* not defined.');
        }

        /**
         * getModel
         *
         * @access public
         * @static
         * @param  string $name
         * @return Model
         */
        public static function getModel($name)
        {
            // model check
            if (isset(self::$_models[$name])) {
                return self::$_models[$name];
            }

            // if the model hasn't already been loaded
            $full = ($name) . 'Model';
            if (!class_exists($full)) {

                // boot model
                require_once APP . '/models/' . ($name) . '.class.php';
            }

            // instantiate model; return
            self::$_models[$name] = (new $full);
            return self::$_models[$name];
        }

        /**
         * getRequest
         *
         * @access public
         * @static
         * @return Request
         */
        public static function getRequest()
        {
            return self::$_request;
        }

        /**
         * getRoutes
         *
         * Returns the array of all routes the application may accept for a
         * request.
         *
         * @access public
         * @static
         * @return array
         */
        public static function getRoutes()
        {
            return self::$_routes;
        }

        /**
         * setRequest
         *
         * @access public
         * @static
         * @param  Request $request
         * @return void
         */
        public static function setRequest(Request $request)
        {
            self::$_request = $request;
        }

        /**
         * setRoutes
         *
         * Sets an array of all possible routes that the request are allowed to
         * match.
         *
         * @access public
         * @static
         * @param  array $routes
         * @return void
         */
        public static function setRoutes(array $routes)
        {
            // reindex array with path (key) set as attribute
            foreach ($routes as $path => &$route) {
                $route['path'] = $path;
            }
            $routes = array_values($routes);

            // store
            self::$_routes = $routes;
        }
    }
