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
         * error
         *
         * 404 requests that come in.
         * 
         * @access public
         * @param  string $path
         * @return void
         */
        public function error($path)
        {
            // agent storage (check done with respect to spiders)
            $agent = isset($_SERVER['HTTP_USER_AGENT'])
                ? $_SERVER['HTTP_USER_AGENT']
                : '(unknown)';

            // log
            error_log(
                "\n" .
                "**Invalid Request**\n" .
                "Path: *" . ($path) . "*\n" .
                "Remote Address: *" . (IP) . "*\n" .
                "Agent: *" . ($agent) ."*\n"
            );
        }

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
