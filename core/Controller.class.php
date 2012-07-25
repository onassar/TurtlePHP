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
     * 
     * @todo switch <isSubRequest> to <Request> class; add <getRequest> method
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
         * @param  Array &$hash
         * @param  Array $key array of keys which are used to make associative
         *         references in <$hash>
         * @param  mixed $value variable which is written to <$hash> reference,
         *         based on $keys as associative indexes
         * @return void
         */
        private function __cascade(array &$hash, array $keys, $value)
        {
            $key = array_shift($keys);
            if (!isset($hash[$key]) || !is_array($hash[$key])) {
                $hash[$key] = array();
            }
            if (!empty($keys)) {
                $this->__cascade($hash[$key], $keys, $value);
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
            // if <$hash> should store <$value> in a child-array
            if (strstr($key, '.')) {
                $keys = explode('.', $key);
                $this->__cascade($this->_hash, $keys, $value);
            
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
            $this->_pass('this', $this);
            $this->_pass('request', $this->_request);
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
