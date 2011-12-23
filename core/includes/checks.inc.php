<?php

    // controllers/views/webroot directories exist
    if (!is_dir(APP . '/controllers')) {
        throw new Exception(APP . '/controllers doesn\'t exist.');
    } elseif (!is_dir(APP . '/views')) {
        throw new Exception(APP . '/views doesn\'t exist.');
    } elseif (!is_dir(APP . '/webroot')) {
        throw new Exception(APP . '/webroot doesn\'t exist.');
    }

    // check application init
    if (!file_exists(APP . '/init.inc.php')) {
        throw new Exception(APP . '/init.inc.php doesn\'t exist.');
    }

    // check appliction routes
    if (!file_exists(APP . '/routes.inc.php')) {
        throw new Exception(APP . '/routes.inc.php doesn\'t exist.');
    }
