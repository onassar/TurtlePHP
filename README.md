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
 - TurltePHP&#039;s core directory contains just 8 files, and is cumulatively
less than 35kb
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
        RewriteRule ^(.*)$ %{DOCUMENT_ROOT}/core/index.php [L,QSA]
        RewriteRule (.*) %{DOCUMENT_ROOT}/application/webroot$1 [L,QSA]
    </VirtualHost>

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

The above \<AppController\> class extends the default Controller class
(specified through the \<Turtle\> namespace), and defines one method:
\<prepare\>.

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
