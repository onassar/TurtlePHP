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
         * @var array
         * @access protected
         * @static
         */
        protected static $_callbacks = array();

        /**
         * _error
         * 
         * @var string (default: 'includes/error.inc.php'
         * @access protected
         * @static
         */
        protected static $_error = 'includes/error.inc.php';

        /**
         * _models
         * 
         * @var array
         * @access protected
         * @static
         */
        protected static $_models = array();

        /**
         * _response
         * 
         * @var string
         * @access protected
         * @static
         */
        protected static $_response;

        /**
         * _route
         * 
         * @var array
         * @access protected
         * @static
         */
        protected static $_route;

        /**
         * _routes
         * 
         * @var array
         * @access protected
         * @static
         */
        protected static $_routes;

        /**
         * addCallback function.
         * 
         * @access public
         * @static
         * @param Closure $callback
         * @return void
         */
        public static function addCallback(\Closure $callback)
        {
            array_push(self::$_callbacks, $callback);
        }

        /**
         * addRoute function.
         * 
         * @access public
         * @static
         * @param array $route
         * @return void
         */
        public static function addRoute(array $route)
        {
            array_unshift(self::$_routes, $route);
        }

        /**
         * getCallbacks function. Returns a reference to the array of callbacks
         *     set up by the application and/or plugins.
         * 
         * @note a reference is returned rather than the native array to allow
         *     for the possibility of a callback adding another response
         *     callback. For an example, see the <Performance> plugin.
         * @access public
         * @static
         * @return array
         */
        public static function &getCallbacks()
        {
            return self::$_callbacks;
        }

        /**
         * getErrorPath function.
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
         * getModel function.
         * 
         * @access public
         * @static
         * @param string $name
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
         * getResponse function.
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
         * getRoute function. Returns the the route that the application has
         *     matched for the request.
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
         * setErrorPath function.
         * 
         * @access public
         * @static
         * @static
         * @param string $path
         * @return void
         */
        public static function setErrorPath($path)
        {
            self::$_error = $path;
        }

        /**
         * getRoutes function. Returns the array of all routes the application
         *     may accept for a request.
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
         * setResponse function. Sets the rendered response for the request.
         * 
         * @access public
         * @static
         * @param string $response
         * @return void
         */
        public static function setResponse($response)
        {
            self::$_response = $response;
        }

        /**
         * setRoute function. Sets the route that the request mathces.
         * 
         * @access public
         * @static
         * @param array $route
         * @return void
         */
        public static function setRoute(array $route)
        {
            self::$_route = $route;
        }

        /**
         * setRoutes function. Sets an array of all possible routes that the
         *     request are allowed to match.
         * 
         * @access public
         * @static
         * @param array $routes
         * @return void
         */
        public static function setRoutes(array $routes)
        {
            self::$_routes = $routes;
        }
    }
