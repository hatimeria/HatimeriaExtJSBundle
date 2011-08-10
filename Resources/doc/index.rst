ExtJSBundle
============

ExtJSBundle is an implementation of Ext Direct (part of ExtJS framework from Sencha) specification for Symfony2
framework.

ExtJS 4: http://docs.sencha.com/ext-js/4-0/

Bundle is highly customized https://github.com/oaugustus/DirectBundle fork.

Requirements
------------
FOSUserBundle is required because of default 403 redirecting to login page

Installing
----------

The best way to install ExtJSBundle into your project is add it as a git submodule.
To do it, in the terminal, go to your main  Symfony2 application directory
(e.g. /home/htdocs/symfony-sandbox or c:\\wamp\\www\\symfony-sandbox) and run:

::

    # add ExtJSBundle as a git submodule into your project
    $ git submodule add git://github.com/hatimeria/HatimeriaExtJSBundle.git vendor/Hatimeria/ExtJSBundle

Register the Hatimeria namespace into your autoloader
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

::

    // app/autoload.php
    $loader->registerNamespaces(array(
        // ...,
        'Hatimeria' => __DIR__.'/../vendor/bundles',
        // ...,
    );

Register ExtJSBundle into your application kernel
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

::

    // app/AppKernel.php
    public function registerBundles()
    {
        $bundles = array(
            // ...,
            new Hatimeria\ExtJSBundle\HatimeriaExtJSBundle(),
            // ...,
        );

        //..
        return $bundles;
    }

Register ExtJSBundle route into your route config
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

::

    // app/config/routing.yml
    # ... your other routes here
    direct:
        resource: "@HatimeriaExtJSBundle/Resources/config/routing.yml"


How to use
----------

Optionally configure singin route
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
::
    // app/config/config.yml
    hatimeria_ext_js:
      signin_route: fos_user_security_login

If direct request got 403 response code it will redirect user to login page

Add the ExtDirect API into your page
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

If you is using Twig engine, only add the follow line in your views page at the
script section:
api - dynamic js file which contains list of available backend actions
direct-api-handler - handle backend errors (show nice error message to user or profiler output to developer)

::

    <script type="text/javascript" src="{{ url('api')}}"></script>
    <script type="text/javascript" src="/bundles/hatimeriaextjs/js/direct-api-handler.js"></script>

Expose your controller methods to ExtDirect Api
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

::

    // ...
    namespace Hatimeria\HelloBundle\Controller;

    class TestController extends Controller
    {
       /*
        * Single exposed method.
        *
        * @remote    // this annotation expose the method to API
        * @param  ParameterBag $params
        * @return string
        */
        public function indexAction($params)
        {
            return 'Hello '.$params['name'];
        }

       /*
        * Grid backend
        *
        * @remote    // this annotation expose the method to API
        * @param  ParameterBag $params
        * @return string
        */
        public function listAction($params)
        {
            // entity must have toStoreArray function which returns it's array representation
            $pager = $this->get('hatimeria_extjs.pager')->create('ExampleCompany\ExampleBundle\Entity\Example', $params);
            // use for sorting - map extjs column name to real entity column name
            $pager->addColumnAlias('createdAt.date', 'createdAt');
            
            $qb = $pager->getQueryBuilder();

            // add filter if there is a name parameter send by javascript
            if (isset($params['name'])) {
                $qb->andWhere('e.name like :name');
                $qb->setParameter('name', '%' . $params['name'] . '%');
            }
            
            return $pager;
        }

        /*
         * An action to handle forms.
         *
         * @remote   // this annotation expose the method to API
         * @form     // this annotation expose the method to API with formHandler option
         * @param ParameterBag $params Form submited values
         * @param array $files  Uploaded files like $_FILES
         */
        public function testFormAction($params, $files)
        {
            // your proccessing

            // Automatic response base on validation result, error list or clean succes message
            return FormResponse($form);
        }
    }

Call the exposed methods from JavaScript
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

::

    // Hello is the Bundle name without 'Bundle'
    // Test is the Controller name without 'Controller'
    // index is the method name without 'Action'
    Actions.Hello_Test.index({name: 'test'}, function(r){
       alert(r);
    });

Finished
~~~~~~~~

Well, this all to ExtJSBundle work. Suggestions, bug reports and observations
are wellcome.