<?php

    /**
     * CommonController class.
     * 
     * @extends Controller
     * @final
     */
    final class CommonController extends \Turtle\Controller
    {
        /**
         * error function.
         * 
         * @access public
         * @param string $path
         * @return void
         */
        public function error($path)
        {
            // log
            error_log(
                "Invalid Request\n" .
                "Path: *" . ($path) . "*\n" .
                "Remote Address: *" . (IP) . "*\n" .
                "Agent: *" . ($_SERVER['HTTP_USER_AGENT']) ."*\n"
            );
        }

        /**
         * index function.
         * 
         * @access public
         * @return void
         */
        public function index()
        {
            $this->_pass('message', 'Hello World!');
        }
    }
