DirectBundle
============

DirectBundle is an implementation of ExtDirect specification to Symfony2
framework.

Installing
----------

The best way to install DirectBundle into your project is add it as a git submodule.
To do it, in the terminal, go to your main  Symfony2 application directory
(e.g. /home/htdocs/symfony-sandbox or c:\\wamp\\www\\symfony-sandbox) and run:

::

    # add DirectBundle as a git submodule into your project
    $ git submodule add git://github.com/oaugustus/DirectBundle.git src/Neton/DirectBundle

Register the Neton namespace into your autoloader
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

::

    // app/autoload.php
    $loader->registerNamespaces(array(
        // ...,
        'Neton' => __DIR__.'/../src',
        // ...,
    );

Register DirectBundle into your application kernel
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

::

    // app/AppKernel.php
    public function registerBundles()
    {
        return array(
            // ...,
            new Neton\DirectBundle\DirectBundle(),
            // ...,
        );
    }

Register DirectBundle route into your route config
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

::

    // app/config/routing.yml
    # ... your other routes here
    direct:
        resource: "@DirectBundle/Resources/config/routing.yml"

Define the ExtDirect Api url to your application
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

::

    // app/config/config.yml
    # ...
    # Direct Configuration
    direct.config:
        api:
            url: http://localhost/symfony-sandbox/web/app.php/route # required
            #remote_attrinute: '@remote'   default value, not required
            #form_attribute:   '@form'     default value, not required
            #type:             remoting    default value, not required
            #namespace:        Actions     default value, not required
            #id:               API         default value, not required
    # ...