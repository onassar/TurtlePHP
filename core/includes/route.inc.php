<?php

    // request/path lookup
    $url = $_SERVER['REQUEST_URI'];
    $url = parse_url($url, PHP_URL_PATH);

    // route retrieval/default
    $routes = \Turtle\Request::getRoutes();

    // route determination
    $matches = array();
    foreach ($routes as $pattern => $details) {
        $pattern = str_replace('/', '\/', $pattern);
        if (preg_match('/' . ($pattern) . '/', $url, $matches)) {
            $route = $details;
            array_shift($matches);
            $route['params'] = $matches;
            break;
        }
    }

    // if no matching route found
    if (!isset($route)) {
        throw new Exception('Matching route could not be found.');
    }

    // mvc mapping
    \Turtle\Request::setRoute($route);
