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
         * _variables
         * 
         * (default value: array())
         * 
         * @var    array
         * @access protected
         */
        protected $_variables = array();

        /**
         * _request
         * 
         * @var    Request
         * @access protected
         */
        protected $_request;

        /**
         * __cascade
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
         * @access private
         * @param  Array &$variables
         * @param  Array $key array of keys which are used to make associative
         *         references in <$variables>
         * @param  mixed $value variable which is written to <$variables>
         *         reference, based on $keys as associative indexes
         * @return void
         */
        private function __cascade(array &$variables, array $keys, $value)
        {
            $key = array_shift($keys);
            if (!isset($variables[$key]) || !is_array($variables[$key])) {
                $variables[$key] = array();
            }
            if (!empty($keys)) {
                $this->__cascade($variables[$key], $keys, $value);
            } else {
                $variables[$key] = $value;
            }
        }

        /**
         * _pass
         * 
         * @access protected
         * @param  String $key
         * @param  mixed $value
         * @return void
         */
        protected function _pass($key, $value)
        {
            // if <$value> should be stored in a child-array
            if (strstr($key, '.')) {
                $keys = explode('.', $key);
                $this->__cascade($this->_variables, $keys, $value);
            
            } else {
                $this->_variables[$key] = $value;
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
         * @access public
         * @return void
         */
        public function prepare()
        {
            $this->_pass('this', $this);
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
