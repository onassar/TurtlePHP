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
         * _pass
         * 
         * @access protected
         * @param  string $key
         * @param  mixed $value
         * @return void
         */
        protected function _pass($key, $value)
        {
            $this->_hash[$key] = $value;
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
