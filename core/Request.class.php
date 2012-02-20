<?php

    // framework namespace
    namespace Turtle;

    /**
     * Request
     * 
     * @abstract
     * @notes    in PHP 5.4.x, $this will be able to be passed into closures
     *           (useful for the <addCallback> method)
     */
    abstract class Request
    {
        /**
         * _callbacks
         * 
         * @var    array
         * @access protected
         * @static
         */
        protected static $_callbacks = array();

        /**
         * _error
         * 
         * @var    string (default: 'includes/error.inc.php'
         * @access protected
         * @static
         */
        protected static $_error = 'includes/error.inc.php';

        /**
         * _models
         * 
         * @var    array
         * @access protected
         * @static
         */
        protected static $_models = array();

        /**
         * _response
         * 
         * @var    string
         * @access protected
         * @static
         */
        protected static $_response;

        /**
         * _route
         * 
         * @var    array
         * @access protected
         * @static
         */
        protected static $_route;

        /**
         * _routes
         * 
         * @var    array
         * @access protected
         * @static
         */
        protected static $_routes;

        /**
         * addCallback
         * 
         * @access public
         * @static
         * @param  Closure $callback
         * @return void
         */
        public static function addCallback(\Closure $callback)
        {
            array_push(self::$_callbacks, $callback);
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
         * @param  array $route
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
         * getCallbacks
         * 
         * Returns a reference to the array of callbacks set up by the
         * application and/or plugins.
         * 
         * @notes  a reference is returned rather than the native array to allow
         *         for the possibility of a callback adding another response
         *         callback. For an example, see the <Performance> plugin.
         * @access public
         * @static
         * @return array
         */
        public static function &getCallbacks()
        {
            return self::$_callbacks;
        }

        /**
         * getErrorPath
         * 
         * @access public
         * @static
         * @return string
         */
        public static function getErrorPath()
        {
            return self::$_error;
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

            // boot model
            require_once APP . '/models/' . ($name) . '.class.php';
            $name .= 'Model';
            self::$_models[$name] = (new $name);
            return self::$_models[$name];
        }

        /**
         * getResponse
         * 
         * @access public
         * @static
         * @return string
         */
        public static function getResponse()
        {
            return self::$_response;
        }

        /**
         * getRoute
         * 
         * Returns the route that the application has matched for the request.
         * 
         * @access public
         * @static
         * @return array
         */
        public static function getRoute()
        {
            return self::$_route;
        }

        /**
         * setErrorPath
         * 
         * @access public
         * @static
         * @param  string $path
         * @return void
         */
        public static function setErrorPath($path)
        {
            self::$_error = $path;
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
         * setResponse
         * 
         * Sets the rendered response for the request.
         * 
         * @access public
         * @static
         * @param  string $response
         * @return void
         */
        public static function setResponse($response)
        {
            self::$_response = $response;
        }

        /**
         * setRoute
         * 
         * Sets the route that the request matches.
         * 
         * @access public
         * @static
         * @param  array $route
         * @return void
         */
        public static function setRoute(array $route)
        {
            self::$_route = $route;
        }

        /**
         * setRoutes
         * 
         * Sets an array of all possible routes that the request are allowed to match.
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
