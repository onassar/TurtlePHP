<?php

    // framework namespace
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
         * _deepMerge
         * 
         * @see     http://www.php.net/manual/en/function.array-merge-recursive.php#92195
         * @access  protected
         * @param   array $array1
         * @param   array $array2
         * @return  array
         */
        protected function _deepMerge(array &$array1, array &$array2): array
        {
            $merged = $array1;
            foreach ($array2 as $key => &$value) {
                if (
                    is_array($value) === true
                    && isset($merged[$key]) === true
                    && is_array($merged[$key]) === true
                ) {
                    $merged[$key] = $this->_deepMerge($merged[$key], $value);
                } else {
                    $merged[$key] = $value;
                }
            }
            return $merged;
        }

        /**
         * _getViewPath
         * 
         * @access  protected
         * @return  null|string
         */
        protected function _getViewPath(): ?string
        {
            $request = $this->_request;
            if ($request === null) {
                return null;
            }
            $route = $request->getRoute();
            $viewPath = $route['view'] ?? null;
            return $viewPath;
        }

        /**
         * _pass
         * 
         * Writes data, recursively if required, to allow variable passing in
         * the following syntax:
         * 
         * $this->_pass('title', 'Hello World!');
         * $this->_pass('meta.description', 'Sample description');
         * $this->_pass('meta', array('author' => 'Oliver Nassar'));
         * 
         * Based on the above syntax, the following variables are available to
         * the view:
         * 
         * $title = 'Hello World!';
         * $meta = array(
         *     'description' => 'Sample description',
         *     'author' => 'Oliver Nassar'
         * );
         * 
         * To overwrite an entire variable, pass in the third optional boolean:
         * 
         * $this->_pass('title', 'Hello World!');
         * $this->_pass('meta.description', 'Sample description');
         * $this->_pass('meta', array('author' => 'Oliver Nassar'), true);
         * 
         * Producing:
         * 
         * $title = 'Hello World!';
         * $meta = array(
         *     'author' => 'Oliver Nassar'
         * );
         * 
         * @throws  \Exception
         * @access  protected
         * @param   string $key
         * @param   mixed $mixed
         * @param   bool $hardSet (default: false)
         * @return  void
         */
        protected function _pass($key, $mixed, $hardSet = false): void
        {
            if ($key === 'controller' || $key === 'request') {
                throw new \Exception('Invalid variable key');
            }

            // value should cascade through to a sub-child
            if (strstr($key, '.') !== false) {
                $keys = explode('.', $key);
                $numberOfKeys = count($keys);
                $variablesDuplicate = $this->_variables;
                $placeholder = &$variablesDuplicate;
                foreach ($keys as $index => $key) {
                    $isLastKey = $index === ($numberOfKeys - 1);
                    if (isset($placeholder[$key]) === false) {
                        if ($isLastKey === false) {
                            $placeholder[$key] = array();
                        }
                    }
                    if ($isLastKey === false) {
                        $placeholder = &$placeholder[$key];
                    }
                }
                $placeholder[$key] = $mixed;

                // any subling values should be overwritten
                if ($hardSet === true) {
                    $this->_variables = $variablesDuplicate;
                } else {
                    $this->_variables = $this->_deepMerge(
                        $this->_variables,
                        $variablesDuplicate
                    );
                }
                unset($variablesDuplicate);

            } else {
                if ($hardSet === true) {
                    $this->_variables[$key] = $mixed;
                } else {
                    $variablesDuplicate = $this->_variables;
                    $variablesDuplicate[$key] = $mixed;
                    $this->_variables = $this->_deepMerge(
                        $this->_variables,
                        $variablesDuplicate
                    );
                    unset($variablesDuplicate);
                }
            }
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
         * actionFour04
         * 
         * 404 requests that come in.
         * 
         * @note    An action that ought to be available to all controllers
         * @param   mixed $stamp variable that is passed along to the error log
         * @param   array $lines (default: array())
         * @access  public
         * @return  void
         */
        public function actionFour04($stamp = null, array $lines = array()): void
        {
            $request = $this->_request;
            $message = $request->getFour04LogMessage($stamp, $lines);
            error_log($message);
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
        public function getGET()
        {
            $request = $this->getRequest();
            if ($request->isSubRequest() === true) {
                $uri = $request->getURI();
                $parsed = parse_url($uri);
                if (isset($parsed['query'])) {
                    parse_str($parsed['query'], $params);
                    return $params;
                }
                $get = array();
                return $get;
            }
            $get = $_GET;
            return $get;
        }

        /**
         * getPOST
         * 
         * Returns the _POST array if the request is *not* a subrequest. If it
         * is a subrequest
         * 
         * @todo    Update so that data can be posted through a Request object,
         *          and returned from here
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
         * Can be useful if defined by an <AppController> child, to perform
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
