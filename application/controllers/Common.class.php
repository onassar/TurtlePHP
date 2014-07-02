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
         * actionIndex
         * 
         * @access public
         * @return void
         */
        public function actionIndex()
        {
            $this->_pass('message', 'Hello World!');
        }
    }
