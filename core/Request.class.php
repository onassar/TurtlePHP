<?php

    // framework namespace
    namespace Turtle;

    /**
     * Request
     * 
     */
    class Request
    {
        /**
         * _callbacks
         * 
         * @access  protected
         * @var     array (default: array())
         */
        protected $_callbacks = array();

        /**
         * _controller
         * 
         * Reference to the Controller that this Request has been routed to.
         * 
         * @access  protected
         * @var     Controller
         */
        protected $_controller;

        /**
         * _createdTimestamp
         * 
         * @access  protected
         * @var     null|float (default: null)
         */
        protected $_createdTimestamp = null;

        /**
         * _errorViewPath
         * 
         * @access  protected
         * @var     string (default: 'error.inc.php')
         */
        protected $_errorViewPath = 'error.inc.php';

        /**
         * _path
         * 
         * @access  protected
         * @var     string
         */
        protected $_path;

        /**
         * _response
         * 
         * @access  protected
         * @var     string
         */
        protected $_response;

        /**
         * _route
         * 
         * @access  protected
         * @var     array
         */
        protected $_route;

        /**
         * _uri
         * 
         * @access  protected
         * @var     string
         */
        protected $_uri;

        /**
         * __construct
         * 
         * @note    <false> check below done since some urls with :12345 can
         *          fail. For example https://i.imgur.com/kPsgsmE.png
         * @access  public
         * @param   string $uri
         * @return  void
         */
        public function __construct($uri)
        {
            $this->_createdTimestamp = microtime(true);
            $this->_uri = $uri;
            $parsed = parse_url($this->_uri, PHP_URL_PATH);
            if ($parsed === false) {
                $parsed = preg_replace('/\?.*/', '', $uri);
            }
            $this->_path = $parsed;
            Application::addRequest($this);
        }

        /**
         * addCallback
         * 
         * @access  public
         * @param   Closure $callback
         * @return  void
         */
        public function addCallback(\Closure $callback): void
        {
            array_push($this->_callbacks, $callback);
        }

        /**
         * boot
         * 
         * This helper is designed to accept specific data, to make including
         * other content/partials a cleaner experience (from PHPs perspective).
         * 
         * @note    This method does *not* receive any variables that were set
         *          by the request-level controller. This was done to prevent
         *          variable-collisions in booted files.
         * @access  public
         * @param   string $path
         * @param   array $data (default: array())
         * @return  void
         */
        public function boot($path, array $data = array()): void
        {
            /**
             * Make call to $this->_controller->setDefaultControllerVariables()
             * to have $controller and $request included by default?
             */

            // closure to clean it up after
            $process = function($__path, array $__variables)
            {
                // bring variables forward
                foreach ($__variables as $__name => $__value) {
                    $$__name = $__value;
                }

                // boot it in
                include $__path;
            };

            // process request; remove closure (memory)
            $response = $process($path, $data);
            unset($process);
        }

        /**
         * generate
         * 
         * Generates the markup for this <Request> instance by routing it
         * through the respective controller.
         * 
         * @access  public
         * @return  void
         */
        public function generate()
        {
            // if it's a redirection, then let's do it
            if (isset($this->_route['redirect']) === true) {

                // Insert variables, if any
                $destination = $this->_route['redirect'];
                if (strstr($destination, '$') !== false) {
                    $params = $this->_route['params'];
                    $destination = preg_replace_callback(
                        '/\$([0-9]+)/',
                        function($matches) use ($params) {
                            return $params[$matches[1] - 1];
                        },
                        $destination
                    );
                }

                // Pass along any relevant headers (eg. satus code)
                if (isset($this->_route['code']) === true) {
                    $code = $this->_route['code'];
                    header('HTTP/1.1 ' . ($code) . ' Moved Permanently');
                }
                if ($destination === '') {
                    $destination = '/';
                }
                header('Location: ' . ($destination));
                exit(0);
            }

            // routing details (excluding the view)
            $action = $this->_route['action'];
            if (is_array($action) === true) {
                $method = $_SERVER['REQUEST_METHOD'];
                $method = strtolower($method);
                if (isset($action[$method]) === false) {
                    echo 'Invalid request method';
                    exit(0);
                }
                $action = $action[$method];
            }
            $controller = $this->_route['controller'];
            $params = $this->_route['params'];

            // if it's *not* a module
            if (
                isset($this->_route['module']) === false
                || $this->_route['module'] === false
            ) {
                // load controller (if not yet loaded)
                if (class_exists(($controller) . 'Controller') === false) {
                    require_once APP . '/controllers/' . ($controller) .
                        '.class.php';
                }
            }

            // new controller reference
            $name = ($controller) . 'Controller';
            $reference = (new $name);
            $reference->setRequest($this);

            // set the controller in this request object
            $this->_controller = $reference;

            /**
             * <prepare> method calling got a little tricky. Namely, if a
             * subrequest was being made, it was naturally getting called twice.
             * The problem with this is that it caused preparation-level code to
             * be executed twice (eg. defining session details, make database
             * calls, etc.). This caused not only flow-problems, but is also not
             * ideal since this data should *already* be available to the
             * controller (since it's already been calculated/retrieved, or
             * what-have-you).
             */

            // if it's not a sub-request
            if ($this->isSubRequest() === false) {
                $callback = array($reference, 'prepare');
                call_user_func_array($callback, array());
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
                $this->getController()->setDefaultControllerVariables();
            }

            // trigger action
            $callback = array($reference, $action);
            call_user_func_array($callback, $params);

            /**
             * Bail if no view is defined; if logic got here, one should be
             * defined. The reason some routes can have just a controller and
             * action defined and not cause an error, is because they redirect
             * in the <call_user_func_array> call.
             * 
             * In the case where they don't, a view is naturally required.
             * 
             * A check could be done here, and a null case <$response> value of
             * an empty string defined, but I don't think there's a point to
             * that.
             */

            /**
             * Grab view (here, instead of above, incase view was changed by
             * controller action)
             */
            $view = $this->_route['view'];

            // if a view was set by a route, or by the controller
            if (isset($view) === true) {

                // controller-set variables
                $variables = $reference->getVariables();

                /**
                 * process
                 * 
                 * Created as a wrapper to prevent global namespace from being
                 * polluted.
                 * 
                 * @access  public
                 * @param   string $__path
                 * @param   array $__variables
                 * @return  string
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
            foreach ($callbacks as $callback) {
                $response = call_user_func($callback, $response);
            }

            // store response
            $this->setResponse($response);
        }

        /**
         * getCallbacks
         * 
         * Returns a reference to the array of callbacks set up by the
         * application and/or plugins.
         * 
         * @note    a reference is returned rather than the native array to
         *          allow for the possibility of a callback adding another
         *          response callback. For an example, see the <Performance>
         *          plugin.
         * @access  public
         * @return  array
         */
        public function &getCallbacks(): array
        {
            $callbacks = $this->_callbacks;
            return $callbacks;
        }

        /**
         * getController
         * 
         * Returns a reference to the controller that this request has been
         * routed to.
         * 
         * @access  public
         * @return  Controller
         */
        public function getController()
        {
            $controller = $this->_controller;
            return $controller;
        }

        /**
         * getCreatedTimestamp
         * 
         * @access  public
         * @return  float
         */
        public function getCreatedTimestamp(): float
        {
            $createdTimestamp = $this->_createdTimestamp;
            return $createdTimestamp;
        }

        /**
         * getErrorPath
         * 
         * @access  public
         * @return  string
         */
        public function getErrorPath(): string
        {
            $errorViewPath = $this->_errorViewPath;
            return $errorViewPath;
        }

        /**
         * getFour04LogMessage
         * 
         * @access  public
         * @param   null|mixed $stamp (default: null)
         * @param   array $lines (default: array())
         * @return  string
         */
        public function getFour04LogMessage($stamp = null, array $lines = array())
        {
            // User agent
            $agent = '(undefined)';
            if (isset($_SERVER['HTTP_USER_AGENT']) === true) {
                $agent = $_SERVER['HTTP_USER_AGENT'];
            }
            $line = array('Agent', $agent);
            array_unshift($lines, $line);

            // Actual (single) IP
            $ip = IP;
            if (strstr($ip, ',') !== false) {
                $ip = strstr($ip, ',', true);
            }
            $line = array('IP', $ip);
            array_unshift($lines, $line);

            // IP
            $ip = IP;
            $line = array('IP Set', $ip);
            array_unshift($lines, $line);

            // Referrer
            $referrer = '(unknown)';
            if (isset($_SERVER['HTTP_REFERER']) === true) {
                $referrer = $_SERVER['HTTP_REFERER'];
            }
            $line = array('Referrer', $referrer);
            array_unshift($lines, $line);

            // Stamp
            $stampValue = '(none)';
            if (is_null($stamp) === false) {
                if (is_string($stamp) === true) {
                    $stampValue = $stamp;
                } elseif (is_array($stamp) === true) {
                    $stampValue = print_r($stamp, true);
                }
            }
            $line = array('Stamp', $stampValue);
            array_unshift($lines, $line);

            // Path
            $path = $_SERVER['REQUEST_URI'];
            $line = array('Path', $path);
            array_unshift($lines, $line);

            // Host
            $host = '(unknown)';
            if (isset($_SERVER['HTTP_HOST']) === true) {
                $host = $_SERVER['HTTP_HOST'];
            }
            $line = array('Host', $host);
            array_unshift($lines, $line);

            // Header
            $header = 'Invalid Request';
            $line = array($header);
            array_unshift($lines, $line);

            // Logging
            $message = "\n";
            $keyMinLength = 20;
            foreach ($lines as $line) {
                if (isset($line[1]) === false) {
                    $message .= '*' . ($line[0]) . '*';
                    $message .= "\n";
                    continue;
                }
                $message .= str_pad('*' . ($line[0]) . '*', $keyMinLength);
                $message .= str_pad(':', 2);
                $message .= $line[1];
                $message .= "\n";
            }

            // Done
            return $message;
        }

        /**
         * getRoutePath
         * 
         * @access  public
         * @return  null|string
         */
        public function getRoutePath(): ?string
        {
            $route = $this->_route ?? null;
            $path = $route['path'] ?? null;
            return $path;
        }

        /**
         * getRoutePathHash
         * 
         * @access  public
         * @return  null|string
         */
        public function getRoutePathHash(): ?string
        {
            $routePath = $this->getRoutePath();
            if ($routePath === null) {
                return null;
            }
            $md5 = md5($routePath);
            $routePathHash = substr($md5, 0, 8);
            return $routePathHash;
        }

        /**
         * getResponse
         * 
         * @access  public
         * @return  null|string
         */
        public function getResponse(): ?string
        {
            $response = $this->_response;
            return $response;
        }

        /**
         * getRoute
         * 
         * Returns the route that the application has matched for the request.
         * 
         * @access  public
         * @return  null|array
         */
        public function getRoute(): ?array
        {
            $route = $this->_route;
            return $route;
        }

        /**
         * getURI
         * 
         * @access  public
         * @return  null|string
         */
        public function getURI(): ?string
        {
            $uri = $this->_uri;
            return $uri;
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
         * @access  public
         * @return  bool
         */
        public function isSubRequest(): bool
        {
            $application = Application::getRequest();
            $isSubRequest = $application !== $this;
            return $isSubRequest;
        }

        /**
         * route
         * 
         * Matches the instance to the appropraite route.
         * 
         * @throws  \Exception
         * @access  public
         * @return  void
         */
        public function route(): void
        {
            // route retrieval/default
            $routes = Application::getRoutes();

            // route determination
            $matches = array();
            foreach ($routes as $details) {

                // Set the default path to look for
                $path = str_replace('/', '\/', $details['path']);
                $resource = $this->_path;

                // If URI matching is to be used
                if (
                    isset($details['resource']) === true
                    && $details['resource'] === 'uri'
                ) {
                    // Ensure it exists (it won't in CLI requests)
                    if (isset($_SERVER['REQUEST_URI']) === true) {
                        $resource = $_SERVER['REQUEST_URI'];
                    } else {

                        // Presumably CLI, so grab uri from there
                        $resource = \Turtle\Loader::getCLIArgument('uri');
                        if ($resource === false) {
                            $resource = $this->_path;
                        }
                    }
                }

                // Case insensitive matching
                $pattern = '/' . ($path) . '/';
                if (true) {
                    $pattern .= 'i';
                }

                // Let's do this!
                if (preg_match($pattern, $resource, $matches)) {
                    $route = $details;
                    array_shift($matches);

                    // route parameter query
                    $params = array();
                    if (isset($route['params']) === true) {

                        // require params to be an array
                        if (is_array($route['params']) === false) {
                            throw new \Exception(
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
            if (isset($route) === false) {
                throw new \Exception('Matching route could not be found.');
            }

            // set matching route
            $this->setRoute($route);
        }

        /**
         * setErrorPath
         * 
         * @access  public
         * @param   string $errorViewPath
         * @return  void
         */
        public function setErrorPath($errorViewPath): void
        {
            $this->_errorViewPath = $errorViewPath;
        }

        /**
         * setResponse
         * 
         * Sets the rendered response for the request.
         * 
         * @access  public
         * @param   string $response
         * @return  void
         */
        public function setResponse($response): void
        {
            $this->_response = $response;
        }

        /**
         * setRoute
         * 
         * Sets the route that the request matches.
         * 
         * @access  public
         * @param   array $route
         * @return  void
         */
        public function setRoute(array $route): void
        {
            $this->_route = $route;
        }
    }
