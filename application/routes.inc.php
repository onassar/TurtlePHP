<?php

    // application routes (parameters are passed directly to controller action)
    $routes = array(

        // home
        '^/$' => array(
            'controller' => 'Common',
            'action' => 'actionIndex',
            'view' => APP . '/views/common/index.inc.php'
        ),

        // error handling
        '(.*)' => array(
            'controller' => 'Common',
            'action' => 'actionFour04',
            'view' => APP . '/views/common/four04.inc.php'
        )
    );

    // route storage
    \Turtle\Application::setRoutes($routes);
