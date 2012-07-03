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
         * addRoutes
         * 
         * @access public
         * @static
         * @param  array $routes
         * @return void
         */
        public static function addRoutes(array $routes)
        {
            // add each route
            foreach ($routes as $path => $route) {
                self::addRoute($path, $route);
            }
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
