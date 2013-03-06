<?php

    // framework namespace
    namespace Turtle;

    /**
     * Controller
     *
     * @see  <http://www.gen-x-design.com/archives/dynamically-add-functions-to-php-classes/>
     *       Idea here is to create an importing system so that plugins can be
     *       created that provide functionality like overriding the <_pass>
     *       method.
     *
     *       This would give the flexibility to add a <partial> method, a'la
     *       Yii, which could extend the <Request> class. This method could
     *       route calls to include a file, creating closures so that only
     *       certain variables are accessible.
     *
     *       Since this is *added* behavior, I like the idea of creating an
     *       importing system to allow this functionality be added through
     *       plugins (or whatever mechanism/naming convention [eg. extension]).
     */
    class Controller
    {
        /**
         * _request
         *
         * @var    Request
         * @access protected
         */
        protected $_request;

        /**
         * _variables
         *
         * (default value: array())
         *
         * @var    array
         * @access protected
         */
        protected $_variables = array();

        /**
         * _cascade
         *
         * Writes data, recursively, to child-array's in order to allow variable
         * passing in the following syntax:
         *
         * $this->_pass('name', 'value');
         * $this->_pass('page.title', 'title');
         *
         * Based on the above syntax, the following variables are available to
         * the view:
         *
         * $name = 'value';
         * $page = array(
         *     'title' => 'title'
         * );
         *
         * @access protected
         * @param  Array &$variables
         * @param  Array $key array of keys which are used to make associative
         *         references in <$variables>
         * @param  mixed $mixed variable which is written to <$variables>
         *         reference, based on $keys as associative indexes
         * @return void
         */
        protected function _cascade(array &$variables, array $keys, $mixed)
        {
            $key = array_shift($keys);
            if (!isset($variables[$key]) || !is_array($variables[$key])) {
                $variables[$key] = array();
            }
            if (!empty($keys)) {
                $this->_cascade($variables[$key], $keys, $mixed);
            } else {
                $variables[$key] = $mixed;
            }
        }

        /**
         * _getModel
         *
         * @access protected
         * @param  string $name
         * @return Model
         */
        protected function _getModel($name)
        {
            return \Turtle\Application::getModel($name);
        }

        /**
         * _getView
         *
         * @access protected
         * @return String
         */
        protected function _getView()
        {
            $route = $this->_request->getRoute();
            return $route['view'];
        }

        /**
         * _pass
         *
         * @access protected
         * @param  String $key
         * @param  mixed $mixed
         * @return void
         */
        protected function _pass($key, $mixed)
        {
            // if <$mixed> should be stored in a child-array
            if (strstr($key, '.')) {
                $keys = explode('.', $key);
                $this->_cascade($this->_variables, $keys, $mixed);

            } else {
                $this->_variables[$key] = $mixed;
            }
        }

        /**
         * _setView
         *
         * @access protected
         * @param  String $path
         * @return void
         */
        protected function _setView($path)
        {
            $route = $this->_request->getRoute();
            $route['view'] = $path;
            $this->_request->setRoute($route);
        }

        /**
         * four04
         *
         * 404 requests that come in.
         * 
         * @note   An action that ought to be available to all controllers
         * @param  mixed $stamp variable that is passed along to the error log
         * @access public
         * @return void
         */
        public function four04($stamp = null)
        {
            // agent storage (check done with respect to spiders)
            $agent = isset($_SERVER['HTTP_USER_AGENT'])
                ? $_SERVER['HTTP_USER_AGENT']
                : '(unknown)';

            // generate the stamp for the log
            $stamped = '(none)';
            if (!is_null($stamp)) {
                if (is_string($stamp)) {
                    $stamped = $stamp;
                } elseif (is_array($stamp)) {
                    $stamped = print_r($stamp, true);
                }
            }

            // set the path that beget this error
            $path = $_SERVER['REQUEST_URI'];

            // log
            error_log(
                "\n" .
                "**Invalid Request**\n" .
                "Path: *" . ($path) . "*\n" .
                "Stamp: *" . ($stamped) . "*\n" .
                "Remote Address: *" . (IP) . "*\n" .
                "Agent: *" . ($agent) ."*\n"
            );
        }

        /**
         * getGet
         *
         * Returns the _GET array if the request is *not* a subrequest. If it
         * is a subrequest, the request URI is parsed and the query passed
         * along is passed back as an array.
         *
         * @access public
         * @return array
         */
        public function getGet()
        {
            $request = $this->getRequest();
            if ($request->isSubRequest() === true) {
                $uri = $request->getUri();
                $parsed = parse_url($uri);
                if (isset($parsed['query'])) {
                    parse_str($parsed['query'], $params);
                    return $params;
                }
                return array();
            }
            return $_GET;
        }

        /**
         * getRequest
         *
         * @access public
         * @return Array
         */
        public function getRequest()
        {
            return $this->_request;
        }

        /**
         * getVariables
         *
         * Returns an array of variables that ought to be available to the view
         * of a controller.
         *
         * @access public
         * @return Array
         */
        public function getVariables()
        {
            return $this->_variables;
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
         * @access public
         * @return void
         */
        public function prepare()
        {
            $this->setDefaultControllerVariables();
        }

        /**
         * setDefaultControllerVariables
         *
         * Sets the default variables that should always be set for a view.
         *
         * @access public
         * @return void
         */
        public function setDefaultControllerVariables()
        {
            $this->_pass('self', $this);
            $this->_pass('request', $this->_request);
        }

        /**
         * setRequest
         *
         * @access public
         * @param  Request $request
         * @return void
         */
        public function setRequest(Request $request)
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
         * @access public
         * @param  Array $variables
         * @return void
         */
        public function setVariables(array $variables)
        {
            $this->_variables = $variables;
        }
    }
