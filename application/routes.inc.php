<?php

    // application routes (params is automated via key-pattern)
    $routes = array(

        // home
        '^/$' => array(
            'controller' => 'Common',
            'action' => 'index',
            'view' => 'common/index.inc.php'
        ),

        // error handling
        '(.*)' => array(
            'controller' => 'Common',
            'action' => 'error',
            'view' => 'common/404.inc.php'
        )
    );

    // route storage
    \Turtle\Request::setRoutes($routes);
