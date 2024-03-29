<?php

    // Namespace overhead
    namespace TurtlePHP;
    use Controller;

    /**
     * Request
     * 
     */
    class Request
    {
        /**
         * _controller
         * 
         * Reference to the Controller that this Request has been routed to.
         * 
         * @access  protected
         * @var     null|\TurtlePHP\Controller (default: null)
         */
        protected $_controller = null;

        /**
         * _hooks
         * 
         * @access  protected
         * @var     array (default: array())
         */
        protected $_hooks = array();

        /**
         * _path
         * 
         * The path that's being requested.
         * 
         * @access  protected
         * @var     null|string (default: null)
         */
        protected $_path = null;

        /**
         * _response
         * 
         * @access  protected
         * @var     null|string (default: null)
         */
        protected $_response = null;

        /**
         * _route
         * 
         * @access  protected
         * @var     null|array (default: null)
         */
        protected $_route = null;

        /**
         * _timestamps
         * 
         * @access  protected
         * @var     array (default: array())
         */
        protected $_timestamps = array();

        /**
         * _uri
         * 
         * @access  protected
         * @var     null|string (default: null)
         */
        protected $_uri = null;

        /**
         * __construct
         * 
         * @access  public
         * @param   string $uri
         * @return  void
         */
        public function __construct(string $uri)
        {
            $this->_timestamps['created'] = microtime(true);
            $this->_uri = $uri;
            $this->_setPath();
            $this->_trackRequest();
        }

        /**
         * _callControllerPrepare
         * 
         * Calls the prepare method for requests that are not sub-requests.
         * 
         * This is to deal with the following case:
         * If a subrequest was made, it was naturally having a prepare call made
         * twice. The problem with that is that it caused preparation-level code
         * to be executed twice (eg. defining session details, make database
         * calls, etc.). This caused not only flow-problems, but was also not
         * ideal since this data should *already* be available to the controller
         * (since it's already been calculated/retrieved, or
         * what-have-you).
         * 
         * @access  protected
         * @return  bool
         */
        protected function _callControllerPrepare(): bool
        {
            if ($this->isSubRequest() === true) {
                return false;
            }
            $controller = $this->_controller;
            $callback = array($controller, 'prepare');
            $args = array();
            call_user_func_array($callback, $args);
            return true;
        }

        /**
         * _generateResponse
         * 
         * @throws  \Exception
         * @access  protected
         * @return  void
         */
        protected function _generateResponse(): void
        {
            $viewPath = $this->getViewPath();
            if ($viewPath === null) {
                $routePath = $this->getRoutePath();
                $msg = 'View not set for route: ' . ($routePath);
                throw new \Exception($msg);
            }
            $controller = $this->_controller;
            $vars = $controller->getVariables();
            $response = \TurtlePHP\Application::renderPath($viewPath, $vars);
            $this->setResponse($response);
        }

        /**
         * _getRouteActionName
         * 
         * @access  protected
         * @return  null|string
         */
        protected function _getRouteActionName(): ?string
        {
            $route = $this->_route;
            $action = $route['action'] ?? null;
            if (is_array($action) === true) {
                $actions = $action;
                $method = $_SERVER['REQUEST_METHOD'];
                $method = strtolower($method);
                $action = $actions[$method] ?? null;
            }
            if (is_callable($action) === true) {
                $routeParams = $this->getRouteParams();
                $args = array($route, $routeParams);
                $action = call_user_func_array($action, $args);
            }
            return $action;
        }

        /**
         * _getRouteBasedMatchPattern
         * 
         * @access  protected
         * @param   array $route
         * @return  string
         */
        protected function _getRouteBasedMatchPattern(array $route): string
        {
            $path = $route['path'];
            $path = str_replace('/', '\/', $path);
            $pattern = '/' . ($path) . '/';
            $pattern .= 'i';
            return $pattern;
        }

        /**
         * _getRouteBasedRequestPath
         * 
         * @access  protected
         * @param   array $route
         * @return  string
         */
        protected function _getRouteBasedRequestPath(array $route): string
        {
            // Bail if the route doesn't say anything about what resource to use
            $requestPath = $this->_path;
            $routeResource = $route['resource'] ?? null;
            if ($routeResource !== 'uri') {
                return $requestPath;
            }

            // Attempt to use the HTTP REQUEST URI path
            $httpRequestURI = $_SERVER['REQUEST_URI'] ?? null;
            if ($httpRequestURI !== null) {
                return $httpRequestURI;
            }

            // Attempt to use the CLI request path
            $cliURI = \TurtlePHP\Loader::getCLIArgument('uri');
            if ($cliURI !== null) {
                return $cliURI;
            }
            return $requestPath;
        }

        /**
         * _getRouteRedirectDestination
         * 
         * @note    The empty redirect destination check is to deal with
         *          redirect patterns that use catch all and replacement params
         *          to redirect, but which can then result in no replacement.
         *          An example would be a redirect route like this:
         *          - https://416.io/ss/x7o7dz
         *          In that case, a URL like the following would result in an
         *          empty redirect destination:
         *          - https://local.getstencil.com/https://getstencil.com
         * @access  protected
         * @return  null|string
         */
        protected function _getRouteRedirectDestination(): ?string
        {
            // No redirect property found in route
            $route = $this->_route;
            $redirectDestination = $route['redirect'] ?? false;
            if ($redirectDestination === false) {
                return null;
            }

            // Closure
            if (is_callable($redirectDestination) === true) {
                $args = $this->getRouteParams();
                // $args = array();
                $redirectDestination = call_user_func_array($redirectDestination, $args);
                return $redirectDestination;
            }

            // Redirect destination does not contain any variables to replace
            if (strstr($redirectDestination, '$') === false) {
                return $redirectDestination;
            }

            // Replace params
            $params = $this->getRouteParams();
            $pattern = '/\$([0-9]+)/';
            $redirectDestination = preg_replace_callback(
                $pattern,
                function(array $matches) use ($params) {
                    $match = $params[$matches[1] - 1];
                    return $match;
                },
                $redirectDestination
            );
            if ($redirectDestination === '') {
                $redirectDestination = '/';
            }
            return $redirectDestination;
        }

        /**
         * _handleSubRequestSetup
         * 
         * Setup a sub-request's variables to come from the initial application
         * request-controller.
         * 
         * @access  protected
         * @return  bool
         */
        protected function _handleSubRequestSetup(): bool
        {
            if ($this->isSubRequest() === false) {
                return false;
            }
            $controller = $this->_controller;
            $request = \TurtlePHP\Application::getRequest();
            $initialController = $request->getController();
            $variables = $initialController->getVariables();
            $controller->setVariables($variables);
            $controller->setDefaultControllerVariables();
            return true;
        }

        /**
         * _processControllerAction
         * 
         * @throws  \Exception
         * @access  protected
         * @return  void
         */
        protected function _processControllerAction(): void
        {
            $controller = $this->_controller;
            $actionName = $this->_getRouteActionName();
            if ($actionName === null) {
                $msg = 'routeActionName value cannot be null';
                throw new \Exception($msg);
            }
            $callback = array($controller, $actionName);
            $params = $this->getRouteParams();
            $args = $params;
            call_user_func_array($callback, $args);
        }

        /**
         * _processRedirect
         * 
         * @access  protected
         * @return  bool
         */
        protected function _processRedirect(): bool
        {
            // Bail if no redirect
            $redirectDestination = $this->_getRouteRedirectDestination();
            if ($redirectDestination === null) {
                return false;
            }

            // Process any headers defined (useful for POST forwarding)
            $route = $this->_route;
            $headers = $route['headers'] ?? array();
            foreach ($headers as $header) {
                header($header);
            }

            // Do the actual redirect (301 permanent redirect is fallback)
            $responseCode = $route['code'] ?? 301;
            $responseCode = (int) $responseCode;
            $this->_setRedirectHeader($redirectDestination, $responseCode);
            exit(0);
        }

        /**
         * _processRoute
         * 
         * @access  protected
         * @return  bool
         */
        protected function _processRoute(): bool
        {
            $this->_setController();
            $this->_callControllerPrepare();
            $this->_handleSubRequestSetup();
            $this->_processControllerAction();
            $this->_generateResponse();
            $this->triggerHooks('process/complete');
            return true;
        }

        /**
         * _routeMatchesRequest
         * 
         * @access  protected
         * @param   array $route
         * @return  bool
         */
        protected function _routeMatchesRequest(array $route): bool
        {
            $pattern = $this->_getRouteBasedMatchPattern($route);
            $requestPath = $this->_getRouteBasedRequestPath($route);
            if (preg_match($pattern, $requestPath) === 1) {
                $evaluator = $route['evaluator'] ?? \TurtlePHP\Application::getRouteEvaluator() ?? null;
                if ($evaluator === null) {
                    return true;
                }
                if (is_bool($evaluator) === true) {
                    return $evaluator;
                }
                $args = array();
                $response = call_user_func_array($evaluator, $args);
                return $response;
            }
            return false;
        }

        /**
         * _setController
         * 
         * @access  protected
         * @return  void
         */
        protected function _setController(): void
        {
            $route = $this->_route;
            $controllerClassName = $route['controller'];
            $controller = new $controllerClassName;
            $controller->setRequest($this);
            $this->_controller = $controller;
        }

        /**
         * _setPath
         * 
         * Attempts to extract the path from the set URI by running it through
         * the parse_url function. That being said, the parse_url fails when the
         * path being passed in looks like:
         * - /app/icons/search/love:123/sub
         * 
         * Specifically, it's the trailing integer after the colon that seems to
         * throw it off. It's likely that it thinks it's a port.
         * 
         * So to deal with this, in cases where parse_url returns false, I
         * simply strip any trailing query string, and assume the path is
         * exactly what's passed in (without attempting to parse it).
         * 
         * So, in summary, the following two paths fail, but have different
         * return values:
         * - /app/icons/search/love:123/sub (return value: false)
         * - //nSerpStat: (return value: null)
         * 
         * @see     https://416.io/ss/pukmji
         * @see     https://416.io/ss/0tpkve
         * @access  protected
         * @return  void
         */
        protected function _setPath(): void
        {
            $uri = $this->_uri;
            $parsedPath = parse_url($uri, PHP_URL_PATH) ?? false;
            if ($parsedPath === false) {
                $pattern = '/\?.*/';
                $replacement = '';
                $parsedPath = preg_replace($pattern, $replacement, $uri);
            }
            $this->_path = $parsedPath;
        }

        /**
         * _setRedirectHeader
         * 
         * @see     https://www.php.net/manual/en/function.header.php
         * @access  protected
         * @param   string $destination
         * @param   int $responseCode
         * @return  void
         */
        protected function _setRedirectHeader(string $destination, int $responseCode): void
        {
            $header = 'Location: ' . ($destination);
            $replace = true;
            header($header, $replace, $responseCode);
        }

        /**
         * _trackRequest
         * 
         * @access  protected
         * @return  void
         */
        protected function _trackRequest(): void
        {
            \TurtlePHP\Application::addRequest($this);
        }

        /**
         * addHook
         * 
         * @access  public
         * @param   string $hookKey
         * @param   callable $callback
         * @return  void
         */
        public function addHook(string $hookKey, callable $callback): void
        {
            $this->_hooks[$hookKey] = $this->_hooks[$hookKey] ?? array();
            array_push($this->_hooks[$hookKey], $callback);
        }

        /**
         * clearHooks
         * 
         * @access  public
         * @param   string $hookKey
         * @return  void
         */
        public function clearHooks(string $hookKey): void
        {
            $this->_hooks[$hookKey] = array();
        }

        /**
         * boot
         * 
         * @note    This method does *not* receive any variables that were set
         *          by the request-level controller. This was done to prevent
         *          variable-collisions in booted files.
         * @access  public
         * @param   string $path
         * @param   array $vars (default: array())
         * @return  void
         */
        public function boot(string $path, array $vars = array()): void
        {
            \TurtlePHP\Application::loadPath($path, $vars);
        }

        /**
         * get404LogMessage
         * 
         * @access  public
         * @return  string
         */
        public function get404LogMessage(): string
        {
            $logPath = \TurtlePHP\Application::get404LogPath();
            $msg = \TurtlePHP\Application::renderPath($logPath);
            return $msg;
        }

        /**
         * getHooks
         * 
         * @access  public
         * @param   string $hookKey
         * @return  array
         */
        public function getHooks(string $hookKey): array
        {
            $hooks = $this->_hooks[$hookKey] ?? array();
            return $hooks;
        }

        /**
         * getController
         * 
         * Returns a reference to the controller that this request has been
         * routed to.
         * 
         * @access  public
         * @return  \TurtlePHP\Controller
         */
        public function getController(): \TurtlePHP\Controller
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
            $createdTimestamp = $this->_timestamps['created'];
            return $createdTimestamp;
        }

        /**
         * getPath
         * 
         * @access  public
         * @return  null|string
         */
        public function getPath(): ?string
        {
            $path = $this->_path;
            return $path;
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
         * Returns an md5 hash of the current matching route, which can be
         * useful in distinguishing different routes in an ambiguous/obfuscated
         * way.
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
         * getRouteParams
         * 
         * Returns an array of params for this request's matching route to be
         * passed to it's associated controller and action.
         * 
         * @note    Route-defined params are prepended to the array to allow for
         *          boolean pattern matches in the routes.
         *          If the route-pattern based params were passed to the
         *          controller-actions first, there could be issues with routes
         *          such as ^/([a-z]?)/path/$
         * @throws  \Exception
         * @access  public
         * @return  array
         */
        public function getRouteParams(): array
        {
            $route = $this->_route;
            $routeParams = $route['params'] ?? array();
            $pattern = $this->_getRouteBasedMatchPattern($route);
            $requestPath = $this->_getRouteBasedRequestPath($route);
            preg_match($pattern, $requestPath, $matches);
            array_shift($matches);
            $pathParams = $matches;
            if (is_callable($routeParams) === true) {
                $routeParams = call_user_func_array($routeParams, $pathParams);
            }
            if (is_array($routeParams) === false) {
                $msg = 'Route params must be an array of values';
                throw new \Exception($msg);
            }
            $params = array_merge($routeParams, $pathParams);
            return $params;
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
         * getViewPath
         * 
         * @access  public
         * @return  null|string
         */
        public function getViewPath(): ?string
        {
            $route = $this->_route;
            $viewPath = $route['view'] ?? null;
            if (is_array($viewPath) === true) {
                $method = $_SERVER['REQUEST_METHOD'];
                $method = strtolower($method);
                $viewPath = $route['view'][$method] ?? null;
            }
            if (is_callable($viewPath) === true) {
                $routeParams = $this->getRouteParams();
                $args = array($route, $routeParams);
                $viewPath = call_user_func_array($viewPath, $args);
            }
            return $viewPath;
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
            $request = \TurtlePHP\Application::getRequest();
            $isSubRequest = $request !== $this;
            return $isSubRequest;
        }

        /**
         * process
         * 
         * Attempts to process a redirect first, and if that isn't relevant,
         * interprets the request as a route to be processed.
         * 
         * @note    Ordered
         * @access  public
         * @return  void
         */
        public function process(): void
        {
            $this->_processRedirect();
            $this->_processRoute();
        }

        /**
         * route
         * 
         * Matches the instance to the appropraite route.
         * 
         * @throws  \Exception
         * @access  public
         * @return  bool
         */
        public function route(): bool
        {
            $routes = \TurtlePHP\Application::getRoutes();
            foreach ($routes as $route) {
                $matches = $this->_routeMatchesRequest($route);
                if ($matches === false) {
                    continue;
                }
                $this->setRoute($route);
                return true;
            }
            $msg = 'Matching route could not be found';
            throw new \Exception($msg);
        }

        /**
         * setNotFoundHeaders
         * 
         * @access  public
         * @return  void
         */
        public function setNotFoundHeaders(): void
        {
            $protocol = $_SERVER['SERVER_PROTOCOL'] ?? 'HTTP/1.1';
            header(($protocol) . ' 404 Not Found');
            header('Status: 404 Not Found');
            header('Content-Type: text/html; charset=utf-8');
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
        public function setResponse(string $response): void
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

        /**
         * setServiceUnavailableHeaders
         * 
         * @link    https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/503
         * @link    https://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html
         * @access  public
         * @param   int $retryAfter (default: 7200)
         * @return  void
         */
        public function setServiceUnavailableHeaders(int $retryAfter = 7200): void
        {
            $protocol = $_SERVER['SERVER_PROTOCOL'] ?? 'HTTP/1.1';
            header(($protocol) . ' 503 Service Temporarily Unavailable');
            header('Status: 503 Service Temporarily Unavailable');
            header('Retry-After: ' . ($retryAfter));
            header('Content-Type: text/html; charset=utf-8');
        }

        /**
         * triggerHooks
         * 
         * @access  public
         * @param   string $hookKey
         * @param   array $args (default: array())
         * @return  void
         */
        public function triggerHooks(string $hookKey, array $args = array()): void
        {
            $args['request'] = $this;
            $hooks = $this->getHooks($hookKey);
            foreach ($hooks as $callback) {
                call_user_func_array($callback, $args);
            }
        }
    }
