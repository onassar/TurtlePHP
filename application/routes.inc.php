<?php

    // application routes (parameters are passed directly to controller action)
    $routes = array(

        // home
        '^/$' => array(
            'controller' => 'Common',
            'action' => 'index',
            'view' => APP . '/views/common/index.inc.php'
        ),

        // error handling
        '(.*)' => array(
            'controller' => 'Common',
            'action' => 'error',
            'view' => APP . '/views/common/404.inc.php'
        )
    );

    // route storage
    \Turtle\Application::setRoutes($routes);
