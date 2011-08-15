# HatimeriaExtJSBundle

HatimeriaExtJSBundle is an implementation of Ext Direct (part of ExtJS framework from Sencha) specification for Symfony2
framework.

[ExtJS 4 documentation](http://docs.sencha.com/ext-js/4-0/)

Bundle is highly customized https://github.com/oaugustus/DirectBundle fork.

## Installing

Follow symfony instructions to add bundle source code from github (use deps)

### Configure autoloader

``` php
<?php
    // app/autoload.php
    $loader->registerNamespaces(array(
        // ...,
        'Hatimeria' => __DIR__.'/../vendor/bundles',
        // ...,
    );
```

### Register bundle


``` php
<?php
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
```

### Register routing


``` yaml
    # app/config/routing.yml
    # ... your other routes here
    direct:
        resource: "@HatimeriaExtJSBundle/Resources/config/routing.yml"
```

## How to use

### Configuration reference

``` yaml
    // app/config/config.yml
    hatimeria_ext_js:
      # If direct request got 403 response code it will redirect user to login page
      signin_route: fos_user_security_login
      javascript_mode: debug # debug | debug-comments | normal - which extjs main file is included
      # nested objects mapping - documentation in progress
      mappings:   
        Example\Example\Entity\User:
            fields: 
              default: [id, username, profile] # profile is object of class Profile
        Example\Example\Entity\Profile:
            fields: 
              default: [id, first_name, last_name]


```

### Add bundle headers to your layout

In your app layout:
{% render "HatimeriaExtJSBundle:Default:headers" %}

### Expose your controller methods to ExtDirect Api


``` php
<?php
    namespace Hatimeria\HelloBundle\Controller;

    use Hatimeria\ExtJSBundle\Response\Failure;
    use Hatimeria\ExtJSBundle\Response\Success;
    use Hatimeria\ExtJSBundle\Response\Form;
    use Hatimeria\ExtJSBundle\Response\Validation;

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
        *
        * @return Validation
        */
        public function validationAction($params)
        {
            // fetch entity, make same changes based on received params from extjs

            $errors = $validator->validate($entity);
        
            return new Validation($errors);
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
            $pager = $this->get('hatimeria_extjs.pager')->fromEntity('ExampleCompany\ExampleBundle\Entity\Example', $params);
            // use for sorting - map extjs column name to real entity column name
            $pager->addColumnAlias('createdAt.date', 'createdAt');

            // this function is called on every record found to make it accesible for json formatter
            // if not function is specified config mappings are used
            $pager->setToStoreFunction(function($entity) { $entity->toStoreArray() });
            
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
            return new Form($form);
        }
    }
```

### Call the exposed methods from JavaScript


``` javascript

    // Hello is the Bundle name without 'Bundle'
    // Test is the Controller name without 'Controller'
    // index is the method name without 'Action'
    Actions.Hello_Test.index({name: 'test'}, function(r){
       alert(r);
    });

    // Show preview grid for list action
    new Hatimeria.grid.Preview(
        {
            directFn: Actions.Hello_Test.list,
            title: 'Example data grid',
            headers: ['Header 1', 'Header 2']
        }
    );
```

## Backend application

Work is in progress.

Example backend base on this bundle:

![Example application screenshot](/https://github.com/hatimeria/HatimeriaExtJSBundle/blob/master/Resources/doc/example.png)

### Finished

Well, this all to HatimeriaExtJSBundle work. Suggestions, bug reports and observations
are wellcome.