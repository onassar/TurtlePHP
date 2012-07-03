<?php

    // framework namespace
    namespace Turtle;

    /**
     * Request
     * 
     * @notes in PHP 5.4.x, $this will be able to be passed into closures
     *        (useful for the <addCallback> method). For now, setting a variable
     *        such as <$self> to <$this> should work (JavaScript styles)
     */
    class Request
    {
        /**
         * _callbacks
         * 
         * @var    array
         * @access protected
         */
        protected $_callbacks = array();

        /**
         * _error
         * 
         * @var    string (default: 'includes/error.inc.php'
         * @access protected
         */
        protected $_error = 'error.inc.php';

        /**
         * _path
         * 
         * @var    string
         * @access protected
         */
        protected $_path;

        /**
         * _response
         * 
         * @var    string
         * @access protected
         */
        protected $_response;

        /**
         * _route
         * 
         * @var    array
         * @access protected
         */
        protected $_route;

        /**
         * _uri
         * 
         * @var    string
         * @access protected
         */
        protected $_uri;

        /**
         * __construct
         * 
         * 
         * 
         * @access public
         * @param  String $uri
         * @return void
         */
        public function __construct($uri)
        {
            $this->_uri = $uri;
            $parsed = parse_url($this->_uri, PHP_URL_PATH);
            $this->_path = $parsed;
        }

        /**
         * addCallback
         * 
         * @access public
         * @param  Closure $callback
         * @return void
         */
        public function addCallback(\Closure $callback)
        {
            array_push($this->_callbacks, $callback);
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
         * @return array
         */
        public function &getCallbacks()
        {
            return $this->_callbacks;
        }

        /**
         * getErrorPath
         * 
         * @access public
         * @return string
         */
        public function getErrorPath()
        {
            return $this->_error;
        }

        /**
         * getResponse
         * 
         * @access public
         * @return string
         */
        public function getResponse()
        {
            return $this->_response;
        }

        /**
         * getRoute
         * 
         * Returns the route that the application has matched for the request.
         * 
         * @access public
         * @return array
         */
        public function getRoute()
        {
            return $this->_route;
        }

        /**
         * generate
         * 
         * Generates the markup for the instance by routing it through the
         * respective controller.
         * 
         * @access public
         * @return void
         */
        public function generate()
        {
            // routing details (excluding the view)
            $action = $this->_route['action'];
            $controller = $this->_route['controller'];
            $params = $this->_route['params'];

            // if it's a plugin
            if (
                isset($this->_route['plugin'])
                && $this->_route['plugin'] === true
            ) {

                // set plugin-controller name
                $name = '\\Plugin\\' . ($controller) . 'Controller';
            }
            // otherwise, standard-flow
            else {

                // load controller
                require_once APP . '/controllers/' . ($controller) .
                    '.class.php';
                $name = $controller . 'Controller';
            }

            // new controller reference
            $reference = (new $name);

            // trigger action
            call_user_func_array(array($reference, 'prepare'), array());
            call_user_func_array(array($reference, $action), $params);
        
            /**
             * Grab view again (here, instead of above, incase view was changed
             * by controller action)
             */
            $view = $this->_route['view'];
        
            // if a view was set by a route, or by the controller
            if (isset($view)) {
        
                // controller-set variables
                $hash = $reference->getHash();
            
                /**
                 * process
                 * 
                 * Created as a wrapper to prevent global namespace from being
                 * polluted.
                 * 
                 * @access public
                 * @param  String $__path
                 * @param  array $__variables
                 * @return string
                 */
                $process = function($__path, array $__variables)
                {
                    // bring variables forward
                    foreach ($__variables as $__name => $__value) {
                        $$__name = $__value;
                    }
            
                    // buffer handling
                    ob_start();
                    include $__path;
                    $__response = ob_get_contents();
                    ob_end_clean();
                    return $__response;
                };
            
                // process request; remove closure (memory)
                $response = $process($view, $hash, $reference);
                unset($process);
            }
            
            // run response through buffer callbacks
            $callbacks = &$this->getCallbacks();
            if (!empty($callbacks)) {
                foreach ($callbacks as $callback) {
                    $response = call_user_func($callback, $response);
                }
            }

            // store response
            $this->setResponse($response);
        }

        /**
         * route
         * 
         * Matches the instance to the appropraite route.
         * 
         * @access public
         * @return void
         */
        public function route()
        {
            // route retrieval/default
            $routes = \Turtle\Application::getRoutes();

            // route determination
            $matches = array();
            foreach ($routes as $details) {
                $path = str_replace('/', '\/', $details['path']);
                if (preg_match('/' . ($path) . '/', $this->_path, $matches)) {
                    $route = $details;
                    array_shift($matches);
                    $route['params'] = $matches;
                    break;
                }
            }

            // if no matching route found
            if (!isset($route)) {
                throw new Exception('Matching route could not be found.');
            }

            // set matching route
            $this->setRoute($route);
        }

        /**
         * setErrorPath
         * 
         * @access public
         * @param  string $path
         * @return void
         */
        public function setErrorPath($path)
        {
            $this->_error = $path;
        }

        /**
         * setResponse
         * 
         * Sets the rendered response for the request.
         * 
         * @access public
         * @param  string $response
         * @return void
         */
        public function setResponse($response)
        {
            $this->_response = $response;
        }

        /**
         * setRoute
         * 
         * Sets the route that the request matches.
         * 
         * @access public
         * @param  array $route
         * @return void
         */
        public function setRoute(array $route)
        {
            $this->_route = $route;
        }
    }
