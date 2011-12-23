<?php

    /**
     * HelperController class.
     * 
     * @extends Controller
     * @final
     */
    final class HelperController extends \Turtle\Controller
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
                "Remote Address: *" . ($_SERVER['REMOTE_ADDR']) . "*\n" .
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
