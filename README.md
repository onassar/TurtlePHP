TurtlePHP
===
TurtlePHP is a PHP MVC-based framework. I use the word framework loosely, as it
contains very few binding features, and uses a minimalistic approach to make
development within it easy and natural (for PHP developers, that is).

### MVC flow
 - Data access ought to be managed through a corresponding object&#039;s model
 - Output of a request ought to contained within a view
 - Business/middleware logic ought to be managed through a controller (and
through a controller&#039;s action/method)

### Buffer control
 - Output buffer can be passed through a single, or series of, closures, to
manipulate output before it is sent to the client
 - Useful for writing framework-wide plugins to (eg. clean output, inject
security headers/content)

### Error/exception handling
 - Programmatic  (eg. not 404) errors are routed to an internal file which
presents a friendly user interface

### Dynamic routing
 - Routes can be added to the application dynamically (eg. by a plugin)
   - Useful in the creation of plugins that provide RESTful functionality

### Tiny footprint
 - TurltePHP&#039;s core directory contains just 6 files, and is cumulatively
less than 20kb
 - While not having been benchmarked, it&#039;s simplicity and minimalism allows
for unencumbered application development

Plugins
===
 - [Logging](https://github.com/onassar/TurtlePHP-LoggingPlugin) Allows for
the modification of PHP&#039;s default error logging
 - [Roles](https://github.com/onassar/TurtlePHP-RolesPlugin) Provides a
standardized way to differentiate between different codebase environments (eg.
local, development, staging, production)
 - [Config](https://github.com/onassar/TurtlePHP-ConfigPlugin) Provides a
standardized approach for storing and retrieving an application&#039;s configuration
settings
 - [Performance](https://github.com/onassar/TurtlePHP-PerformancePlugin)
Analyzes a response that is ready for flushing, determines it&#039;s processing
duration and memory usage, and returns them through custom response-headers
 - [Template](https://github.com/onassar/TurtlePHP-TemplatePlugin)
Passes buffer through a templating-engine to convert custom-tags
programatically. Has the
[PHP-Template](https://github.com/onassar/PHP-Template) library as a
requirement
 - [Error](https://github.com/onassar/TurtlePHP-ErrorPlugin)
Still under development, this plugin modifies the framework's default error handling behaviour (which is to render the output of the included `error.inc.php` file), by outputting a friendly UI which includes the error message, file, line number, and code snippet. Stack trace will be added soon as well.

Implementation
===

### Get Started

The following is what&#039;s required to get your application up and
running with a Hello World example ready to go.  
Add a virtual host for your site, as follows:

    <VirtualHost *:80>
        ServerName hostname.com
        ServerAlias www.hostname.com
        DocumentRoot /var/www/directory

        # turtle routing
        RewriteEngine On
        RewriteCond %{DOCUMENT_ROOT}/application/webroot%{REQUEST_URI} !-f
        RewriteCond %{DOCUMENT_ROOT}/application/webroot%{REQUEST_URI} !-d
        RewriteRule ^(.*)$ %{DOCUMENT_ROOT}/core/index.php [L,QSA]
        RewriteRule (.*) %{DOCUMENT_ROOT}/application/webroot$1 [L,QSA]
    </VirtualHost>

### Sessions
While not a core requirement for setting up a TurtlePHP project, sessions are
ubiquitous with web requests nowadays. I use my
[PHP-SecureSessions](https://github.com/onassar/PHP-SecureSessions) library for
this, and it works pretty well.

If your infrastructure is distributed, the `SMSession` class ought to be used,
but requires memcached to be installed on all servers/instances.

### Controller Extending
You may find it useful to extend the default controller for your application. A
sample of such a case would be as follows:

``` php
<?php

    /**
     * AppController class.
     * 
     * @extends \Turtle\Controller
     */
    class AppController extends \Turtle\Controller
    {
        /**
         * prepare
         * 
         * @access public
         * @return void
         */
        public function prepare()
        {
            $authenticated = false;
            if (
                isset($_SESSION['authenticated'])
                && $_SESSION['authenticated'] === true
            ) {
                $authenticated = true;
            }
            $this->_pass('authenticated', $authenticated);
            parent::prepare();
        }
    }

```

The above `AppController` class extends the default `Controller` class
(specified through the `Turtle` namespace), and defines one method: `prepare`.

This method is processed before a child controller&#039;s action during a
request flow, and allows you to include logic that should be processed
application-wide.

A sample implementation of this application-level controller:

``` php

<?php

    // dependency
    require_once 'App.class.php';

    /**
     * CommonController
     * 
     * Common requests that most applications ought to facilitate.
     * 
     * @extends AppController
     * @final
     */
    final class CommonController extends AppController
    {
    }

```

### Sub-Requests
You are now able to make sub-requests from within a controller using the
following format:

``` php

<?php

    // grab new response
    $subrequest = (new \Turtle\Request('/path/?including=params'));
    $subrequest->route();
    $subrequest->generate();
    $response = $subrequest->getResponse();

```

Want to have this sub-request run through the
[templating plugin](https://github.com/onassar/TurtlePHP-TemplatePlugin)?

``` php

<?php

    // grab new response
    $subrequest = (new \Turtle\Request('/path/?including=params'));
    (new \Plugin\Template($subrequest));
    $subrequest->route();
    $subrequest->generate();
    $response = $subrequest->getResponse();

```

Note that sub-requests will have access to all data that are meant to be passed
to a parent-request's view using the `_pass` method.

That means, that if a variable is passed before the sub-request is initiated,
the sub-request will have access to it.

Additionally, any variables that the sub-request *itself* passes will be made
available to the view that the originating-request/controller gets rendered
under.

It's curious (yet most of the time, irrelevant) functionality that is required
to keep the flow for sub-requests clean, and prevent any unnecessary calls to
the framework native `<prepare>` method.

### Controller/View Variable Passing
From within a controller action/method, variables can be passed as follows:

    $this->_pass('name', 'Oliver Nassar');
    $this->_pass('page.title', 'Webpage Title');
    $this->_pass('page.description', 'Sample description');

Respectively, the following variables are made available in the view:

    $name = 'Oliver Nassar';
    $page = array(
        'title' => 'Webpage Title',
        'description' => 'Sample description'
    );

Note that the period prompts the storage-system to store the value in a
child-array, if it hasn't yet been defined.

Hooks
===

Hooks can be added to the `Application`  
By adding a hook (by passing a `name` and valid callback array to `addHook`), you are registering it with the application to be used application-wide

One example that is in use immediately is an error hook  
Any error hooks added will be run after an error has occured through the fundamental `proxy` function that is part of this framework

The goal with hooks is to give framework-level access to non-core pieces of code (eg. controllers, plugins, modules, etc.)


WordPress Integration
===

To have a WordPress install live at eg. `/blog/`, here's what's needed:

1) `wget` the zip to `.../webroot/blog/`  
2) Add a `.htaccess` file to `.../webroot/blog` with `DirectoryIndex index.php`  
3) Add the following to the `VirtualHost` entry for the site, before the Turtle routing (eg. http://i.imgur.com/pUlnjzU.png):

    # blog
    RewriteCond %{DOCUMENT_ROOT}/application/webroot%{REQUEST_URI} !-f
    RewriteCond %{DOCUMENT_ROOT}/application/webroot%{REQUEST_URI} !-d
    RewriteRule ^/blog/.* %{DOCUMENT_ROOT}/application/webroot/blog/index.php [L]

That should do it!
