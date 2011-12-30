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
         * @param  string $path
         * @return void
         */
        protected function _setView($path)
        {
            $route = \Turtle\Request::getRoute();
            $route['view'] = $path;
            \Turtle\Request::setRoute($route);
        }

        /**
         * getHash
         * 
         * Returns a hash of all variables that ought to be available to the
         * view of a controller.
         * 
         * @access public
         * @return array
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
    }
