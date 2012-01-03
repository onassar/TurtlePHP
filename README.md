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
