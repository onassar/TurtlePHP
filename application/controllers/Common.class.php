<?php

    /**
     * CommonController
     * 
     * Common requests that most applications ought to facilitate.
     * 
     * @extends Controller
     * @final
     */
    final class CommonController extends \Turtle\Controller
    {
        /**
         * index
         * 
         * @access public
         * @return void
         */
        public function index()
        {
            $this->_pass('message', 'Hello World!');
        }
    }
