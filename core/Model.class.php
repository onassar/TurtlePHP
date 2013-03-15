<?php

    // framework namespace
    namespace Turtle;

    /**
     * Model
     * 
     */
    class Model
    {
        /**
         * _getModel
         *
         * @access protected
         * @param  string $name
         * @return Model
         */
        protected function _getModel($name)
        {
            return \Turtle\Application::getModel($name);
        }
    }
