<?php

    // application routes (params is automated via key-pattern)
    $routes = array(

        // home
        '^/$' => array(
            'controller' => 'Helper',
            'action' => 'index',
            'view' => 'helper/index.inc.php'
        ),

        // error handling
        '(.*)' => array(
            'controller' => 'Helper',
            'action' => 'error',
            'view' => 'helper/404.inc.php'
        )
    );

    // route storage
    \Turtle\Request::setRoutes($routes);
