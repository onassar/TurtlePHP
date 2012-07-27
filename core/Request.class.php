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
         * _controller
         * 
         * Reference to the Controller that this Request has been routed to.
         * 
         * @var    Controller
         * @access protected
         */
        protected $_controller;

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
         * getController
         * 
         * Returns a reference to the controller that this request has been
         * routed to.
         * 
         * @access public
         * @return Controller
         */
        public function getController()
        {
            return $this->_controller;
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
         * Generates the markup for this <Request> instance by routing it
         * through the respective controller.
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

            // if it's *not* a module
            if (
                !isset($this->_route['module'])
                || $this->_route['module'] === false
            ) {
                // load controller
                require_once APP . '/controllers/' . ($controller) .
                    '.class.php';
            }

            // new controller reference
            $name = $controller . 'Controller';
            $reference = (new $name);
            $reference->setRequest($this);

            // set the controller in this request object
            $this->_controller = $reference;

            /**
             * <prepare> method calling got a little tricky. Namely, if a
             * subrequest was being made, it was naturally getting called twice.
             * The problem with this is that it caused preparation-level code to
             * be executed twice (eg. defining session details, make db calls,
             * etc.). This caused not only flow-problems, but is also not ideal
             * since this data should *already* be available to the controller
             * (since it's already been calculated/retrieved, or what-have-you).
             */

            // if it's not a sub-request
            if ($this->isSubRequest() === false) {
                call_user_func_array(array($reference, 'prepare'), array());
            }
            /**
             * Otherwise if it is, ensure it has the variables made available
             * through the application-request Controller
             */
            else {
                $request = Application::getRequest();
                $origin = $request->getController();
                $variables = $origin->getVariables();
                $reference->setVariables($variables);
            }

            // trigger action
            call_user_func_array(array($reference, $action), $params);

            /**
             * Grab view (here, instead of above, incase view was changed by
             * controller action)
             */
            $view = $this->_route['view'];

            // if a view was set by a route, or by the controller
            if (isset($view)) {

                // controller-set variables
                $variables = $reference->getVariables();

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
                $response = $process($view, $variables);
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
         * isSubRequest
         * 
         * Returns whether or not <$this> is a subrequest off of a parent
         * <Request> instance/object.
         * 
         * Useful for securing requests that should only be accessible from
         * within the application logic.
         * 
         * @access public
         * @return Boolean
         */
        public function isSubRequest()
        {
            // compare to application-request
            $application = \Turtle\Application::getRequest();
            return $application !== $this;
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

                    // route parameter query
                    $params = array();
                    if (isset($route['params'])) {

                        // require params to be an array
                        if (!is_array($route['params'])) {
                            throw new Exception(
                                'Route parameters are required to be an ' .
                                'array of values.'
                            );
                        }

                        // set them
                        $params = $route['params'];
                    }

                    /**
                     * Prepend the *route-defined* parameters to the params
                     * array, to allow for boolean pattern matches in the
                     * routes.
                     * 
                     * If the route pattern-matches were passed to the
                     * controller-actions first, there could be issues with
                     * routes such as ^/([a-z]?)/path/$
                     */
                    $route['params'] = array_merge($params, $matches);
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
