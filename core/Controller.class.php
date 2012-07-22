<?php

    // framework namespace
    namespace Turtle;

    /**
     * Controller
     * 
     */
    class Controller
    {
        /**
         * _hash
         * 
         * (default value: array())
         * 
         * @var    array
         * @access protected
         */
        protected $_hash = array();

        /**
         * _request
         * 
         * @var    Request
         * @access protected
         */
        protected $_request;

        /**
         * __inject
         * 
         * @access private
         * @param  Array $hash
         * @param  String $key
         * @param  mixed $value
         * @return void
         */
        private function __inject(&$hash, $keys, $value)
        {
            $key = array_shift($keys);
            if (!isset($hash[$key]) || !is_array($hash[$key])) {
                $hash[$key] = array();
            }
            if (!empty($keys)) {
                $this->__inject($hash[$key], $keys, $value);
            } else {
                $hash[$key] = $value;
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
            if (strstr($key, '.')) {
                $keys = explode('.', $key);
                $this->__inject($this->_hash, $keys, $value);
            
            } else {
                $this->_hash[$key] = $value;
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
         * getHash
         * 
         * Returns a hash of all variables that ought to be available to the
         * view of a controller.
         * 
         * @access public
         * @return Array
         */
        public function getHash()
        {
            return $this->_hash;
        }

        /**
         * isSubRequest
         * 
         * Returns whether or not the request being made against this controller
         * is a subrequest from a parent <Request> instance/object.
         * 
         * Useful for securing requests that should only be accessible from
         * within the application logic.
         * 
         * @access public
         * @return Boolean
         */
        public function isSubRequest()
        {
            // get both <Application> and <Controller> requests
            $application = \Turtle\Application::getRequest();
            $controller = $this->_request;

            // return whether the controllers request matches the applications
            return $application !== $controller;
        }

        /**
         * prepare
         * 
         * @access public
         * @return void
         */
        public function prepare()
        {
            $class = get_class($this);
            $class = strtolower($class);
            $class = str_replace('controller', '', $class);
            $this->_pass('controller', $class);
        }

        /**
         * setHash
         * 
         * Overwrites the <_hash> property of this Controller object with an
         * array of data. Currently used by TurtlePHP with respect to
         * sub-requests which need to have access to variables that were
         * set/passed by the origin-controlller.
         * 
         * Probably shouldn't be used outside of TurtlePHP's core files.
         * 
         * @access public
         * @param  Array $hash
         * @return void
         */
        public function setHash(array $hash)
        {
            $this->_hash = $hash;
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
    }
