<?php

    // Namespace overhead
    namespace TurtlePHP;

    /**
     * Controller
     * 
     * @see     <http://www.gen-x-design.com/archives/dynamically-add-functions-to-php-classes/>
     *          Idea here is to create an importing system so that plugins can
     *          be created that provide functionality like overriding the
     *          <_pass> method.
     * 
     *          This would give the flexibility to add a <partial> method, a'la
     *          Yii, which could extend the <Request> class. This method could
     *          route calls to include a file, creating closures so that only
     *          certain variables are accessible.
     * 
     *          Since this is *added* behavior, I like the idea of creating an
     *          importing system to allow this functionality be added through
     *          plugins (or whatever mechanism/naming convention [eg.
     *          extension]).
     * @extends Base
     */
    class Controller extends Base
    {
        /**
         * _request
         * 
         * @access  protected
         * @var     null|Request (default: null)
         */
        protected $_request = null;

        /**
         * _variables
         * 
         * @access  protected
         * @var     array (default: array())
         */
        protected $_variables = array();

        /**
         * _getIndexedKeys
         * 
         * Returns an array of keys that is numerically-indexed to ensure a
         * consistent approach to working with the keys passed in.
         * 
         * @access  protected
         * @param   array $keys
         * @return  array
         */
        protected function _getIndexedKeys(array $keys): array
        {
            if (count($keys) === 0) {
                return $keys;
            }
            $keys = implode('.', $keys);
            $keys = explode('.', $keys);
            return $keys;
        }

        /**
         * _pass
         * 
         * Writes data, recursively if required, to allow variable passing in
         * the following syntax:
         * 
         * $this->_pass('title', 'Hello World!');
         * $this->_pass('meta.author', 'Oliver Nassar');
         * $this->_pass('meta.description', 'Sample description');
         * 
         * Based on the above syntax, the following variables are available to
         * the view:
         * 
         * $title = 'Hello World!';
         * $meta = array(
         *     'author' => 'Oliver Nassar',
         *     'description' => 'Sample description'
         * );
         * 
         * @throws  \Exception
         * @access  protected
         * @param   mixed $keys
         * @param   mixed $incomingValue
         * @return  void
         */
        protected function _pass($keys, $incomingValue): void
        {
            $keys = (array) $keys;
            $indexedKeys = $this->_getIndexedKeys($keys);
            $value = &$this->_variables;
            foreach ($indexedKeys as $key) {
                if ($this->_validVariableKey($key) === false) {
                    $msg = 'Invalid variable key name: ' . ($key);
                    throw new \Exception($msg);
                }
                if (isset($value[$key]) === false) {
                    $value[$key] = array();
                    $value = &$value[$key];
                    continue;
                }
                $value = &$value[$key];
            }
            $value = $incomingValue;
        }

        /**
         * _setView
         * 
         * @access  protected
         * @param   string $path
         * @return  bool
         */
        protected function _setView(string $path): bool
        {
            $request = $this->_request;
            if ($request === null) {
                return false;
            }
            $route = $request->getRoute();
            $route['view'] = $path;
            $request->setRoute($route);
            return true;
        }

        /**
         * _validVariableKey
         * 
         * @access  protected
         * @param   string $key
         * @return  bool
         */
        protected function _validVariableKey(string $key): bool
        {
            if ($key === 'controller') {
                return false;
            }
            if ($key === 'request') {
                return false;
            }
            return true;
        }

        /**
         * action404
         * 
         * @access  public
         * @param   string $requestPath
         * @return  void
         */
        public function action404(string $requestPath): void
        {
            $request = $this->_request;
            $msg = $request->get404LogMessage();
            error_log($msg);
            $request->setNotFoundHeaders();
        }

        /**
         * getGET
         * 
         * Returns the _GET array if the request is *not* a subrequest. If it
         * is a subrequest, the request url is parsed and the query passed
         * along is passed back as an array.
         * 
         * @access  public
         * @return  array
         */
        public function getGET(): array
        {
            $request = $this->getRequest();
            if ($request->isSubRequest() === false) {
                $get = $_GET;
                return $get;
            }
            $uri = $request->getURI();
            $parsed = parse_url($uri);
            if (isset($parsed['query']) === true) {
                parse_str($parsed['query'], $params);
                return $params;
            }
            $get = array();
            return $get;
        }

        /**
         * getPOST
         * 
         * @access  public
         * @return  array
         */
        public function getPOST()
        {
            $request = $this->getRequest();
            if ($request->isSubRequest() === true) {
                $post = array();
                return $post;
            }
            $post = $_POST;
            return $post;
        }

        /**
         * getRequest
         * 
         * @access  public
         * @return  null|\TurtlePHP\Request
         */
        public function getRequest(): ?\TurtlePHP\Request
        {
            $request = $this->_request;
            return $request;
        }

        /**
         * getVariables
         * 
         * Returns an array of variables that ought to be available to the view
         * of a controller.
         * 
         * @access  public
         * @return  array
         */
        public function getVariables(): array
        {
            $variables = $this->_variables;
            return $variables;
        }

        /**
         * prepare
         * 
         * Called before a controller action is, sets up logic that may be
         * needed by the time the action is triggered.
         * 
         * Can be useful if defined by an <Controller\App> child, to perform
         * checks against authenticated users/sessions, load global data from
         * a database and send it to the view, etc.
         * 
         * @access  public
         * @return  void
         */
        public function prepare(): void
        {
            $this->setDefaultControllerVariables();
        }

        /**
         * setDefaultControllerVariables
         * 
         * Sets the default variables that should always be set for a view.
         * 
         * @access  public
         * @return  void
         */
        public function setDefaultControllerVariables(): void
        {
            $this->_variables['controller'] = $this;
            $this->_variables['request'] = $this->_request;
        }

        /**
         * setRequest
         * 
         * @access  public
         * @param   \TurtlePHP\Request $request
         * @return  void
         */
        public function setRequest(\TurtlePHP\Request $request): void
        {
            $this->_request = $request;
        }

        /**
         * setVariables
         * 
         * Overwrites the <_variables> property of this Controller-object with
         * an array of data. Currently used by TurtlePHP with respect to
         * sub-requests which need to have access to variables that were
         * set/passed by the origin-controlller.
         * 
         * Generally, but not always, should be restricted to use within
         * TurtlePHPs core-files.
         * 
         * @access  public
         * @param   array $variables
         * @return  void
         */
        public function setVariables(array $variables): void
        {
            $this->_variables = $variables;
        }
    }
