<?php

    // prepare response
    $response = '';

    // grab routing details (excluding the view)
    $route = \Turtle\Request::getRoute();
    $action = $route['action'];
    $controller = $route['controller'];
    $params = $route['params'];

    // load controller
    require_once APP . '/controllers/' . ($controller) . '.class.php';
    $name = $controller . 'Controller';
    $reference = (new $name);

    // trigger action
    call_user_func_array(array($reference, 'prepare'), array());
    call_user_func_array(array($reference, $action), $params);

    // grab route again (incase view was changed by controller action)
    $route = \Turtle\Request::getRoute();

    // if a view was set by a route, or by the controller
    if (isset($route['view'])) {

        // grab view
        $path = $route['view'];

        // controller-set variables
        $hash = $reference->getHash();
    
        /**
         * process function.
         * 
         * @note prevents global namespace from being polluted
         * @access public
         * @param String $__path
         * @param array $__variables
         * @return string
         */
        $process = function($__path, array $__variables)
        {
            // bring variables forward
            foreach ($__variables as $__name => $__value) {
                $$__name = $__value;
            }
    
            // buffer handling
            ob_start();
            include $__path;
            $__response = ob_get_contents();
            ob_end_clean();
            return $__response;
        };
    
        // process request; remove closure (memory)
        $response = $process($path, $hash, $reference);
        unset($process);
    }
    
    // run response through buffer callbacks
    $callbacks = &\Turtle\Request::getCallbacks();
    if (!empty($callbacks)) {
        foreach ($callbacks as $callback) {
            $response = call_user_func($callback, $response);
        }
    }

    // store response
    \Turtle\Request::setResponse($response);
