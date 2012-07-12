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
