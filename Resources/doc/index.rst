```ruby
require 'redcarpet'
markdown = Redcarpet.new("Hello World!")
puts markdown.to_html
```

ExtJSBundle
============

ExtJSBundle is an implementation of Ext Direct (part of ExtJS framework from Sencha) specification for Symfony2
framework.

ExtJS 4: http://docs.sencha.com/ext-js/4-0/

Bundle is highly customized https://github.com/oaugustus/DirectBundle fork.

Installing
----------

Follow symfony istructions to add bundle source code from github (use deps)

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

Add Javascript files to your layout
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

    use Hatimeria\ExtJSBundle\Response\Failure;
    use Hatimeria\ExtJSBundle\Response\Success;
    use Hatimeria\ExtJSBundle\Validation\FormResponse;
    use Hatimeria\ExtJSBundle\Validation\ValidationResponse;

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
        * Single exposed method with no custom response
        *
        * @remote    // this annotation expose the method to API
        * @param  ParameterBag $params
        * @return string
        */
        public function successAction($params)
        {
            // processing without return statement will generate direct success response
        }

       /*
        * Single exposed method with fail or success message
        *
        * @remote    // this annotation expose the method to API
        * @param  ParameterBag $params
        * @return string
        */
        public function simpleAction($params)
        {
            if(some_condition) {
                return Success;
            } else {
                return Failure;
            }
        }

       /*
        * Validation on entity
        *
        * @remote    // this annotation expose the method to API
        * @param  ParameterBag $params
        * @return string
        */
        public function validationAction($params)
        {
            // fetch entity, make same changes based on received params from extjs

            $errors = $validator->validate($entity);
        
            return ValidationResponse($errors);
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

            // Automatic response based on validation result, error list or clean succes message
            return FormResponse($form);
        }
    }

Call the exposed methods from JavaScript
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

```javascript
    // Hello is the Bundle name without 'Bundle'
    // Test is the Controller name without 'Controller'
    // index is the method name without 'Action'
    Actions.Hello_Test.index({name: 'test'}, function(r){
       alert(r);
    });
```

Finished
~~~~~~~~

Well, this all to ExtJSBundle work. Suggestions, bug reports and observations
are wellcome.