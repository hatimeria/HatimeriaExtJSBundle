# HatimeriaExtJSBundle

HatimeriaExtJSBundle is an implementation of Ext Direct (part of ExtJS framework from Sencha) specification for Symfony2
framework.

[ExtJS 4 documentation](http://docs.sencha.com/ext-js/4-0/)

## Installing

Follow symfony instructions to add bundle source code from github (use deps)

### Requirements

* [BazingaExposeTranslationBundle](https://github.com/bazinga/BazingaExposeTranslationBundle)
* [FOSJsRoutingBundle](https://github.com/FriendsOfSymfony/FOSJsRoutingBundle.git)

Optional:

Pager needs [StofDoctrineExtensionsBundle](https://github.com/stof/StofDoctrineExtensionsBundle) with enabled Paginate extension to work.


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
    HatimeriaExtJSBundle:
        resource: "@HatimeriaExtJSBundle/Resources/config/routing.yml"
```

### Add bundle headers to your layout

In your app layout:
{% render "HatimeriaExtJSBundle:Default:headers" %}

## How to use

### Configuration reference

``` yaml
    // app/config/config.yml
    hatimeria_ext_js:
      # optional setting for production optimization, first from list is used when user locale is not in this array
      locales: ['pl','en']
      # compile all javascripts into one file - use it only in config_prod.yml
      compile: true
      # If direct request got 403 response code it will redirect user to login page
      signin_route: fos_user_security_login
      # optionally your extjs library web path directory (if you use different version than this bundle provides)
      javascript_vendor_path: "bundles/hatimeriaextjs/js/vendor/ext-4.0.7/" # default
      # one of extjs main filenames, [options listed here](http://www.sencha.com/blog/using-ext-loader-for-your-application/)
      javascript_mode:  ext-all-debug # default
      mappings:   
        Example\Example\Entity\User:
            fields: 
              # profile is object of class Profile, account is object which have getBalance method, 
              # createdAt is a DateTime member of user class
              default: [id, username, profile, account.balance, created_at] 
              # fields only visible for admin user
              admin: [password]
        Example\Example\Entity\Profile:
            fields: 
              default: [id, first_name, last_name]


```

### Mappings explained

When record is returned by controller (or pager results) every single object have to be converted to json format (array first).
Because of that you need to specify which object property paths include in results. For example if you need converter like:

``` php
<?php
    
    // In entity class

    public function toArray()
    {
        return array(
            'id'              => $this->id,
            'username'        => $this->username,
            'profile'         => $this->getProfile()->toArray(),
            'account.balance' => $this->getAccount()->getBalance(),
            'created_at'      => $this->getCreatedAt()->format('Y-m-d')
        );
    }
```

You can just configure this behaviour in yml. Related objects are automatically converted,
 (when profile object is found in user the Profile class mappings are used)

### Expose controller - in many ways


``` php
<?php
    namespace Hatimeria\HelloBundle\Controller;

    use Hatimeria\ExtJSBundle\Response\Failure;
    use Hatimeria\ExtJSBundle\Response\Success;
    use Hatimeria\ExtJSBundle\Response\Form;
    use Hatimeria\ExtJSBundle\Response\Validation;
    use Hatimeria\ExtJSBundle\Annotation\Remote as remote;
    use Hatimeria\ExtJSBundle\Annotation\Remote as form;

    use Symfony\Bundle\FrameworkBundle\Controller\Controller;

    class TestController extends Controller
    {
       /**
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

       /**
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

       /**
        * Single exposed method with fail or success message
        *
        * @remote    // this annotation expose the method to API
        * @param  ParameterBag $params
        * @return string
        */
        public function simpleAction($params)
        {
            if($some_condition) {
                return new Success;
            } else {
                return new Failure;
            }
        }

       /**
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

       /**
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
            $pager->setToStoreFunction(function($entity) { $entity->toStoreArray(); });
            
            $qb = $pager->getQueryBuilder();

            // add filter if there is a name parameter send by javascript
            if (isset($params['name'])) {
                $qb->andWhere('e.name like :name');
                $qb->setParameter('name', '%' . $params['name'] . '%');
            }
            
            return $pager;
        }

        /**
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

            // csrf protection must be disabled in this form, whole application, token transport to extjs is not implemented right now
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

    // Show preview grid for list action - example
    Ext.onReady(function() {
        var usersGrid = Ext.create('HatimeriaCore.grid.Preview',
                {
                    directFn: Actions.HatimeriaAdmin_User.list,
                    title: 'Users',
                    headers: {'id': '', 'username': 'Username', 'email': 'Email'}
                }
            );

        usersGrid.init()
    });
```

## Common errors

Annotation not imported or method is not available remotely:
Make sure method comment looks exactly like above example, no extra lines, spaces etc.

## Error handling

When ajax request got symfony exception output JS Direct Api Handler will render it to Developer in nice popup window.
Same goes with fatal, notices and warnings.
If this happens in non dev environment popup window contains only simple error message suitable for normal user.

## Extjs controller for easily running extjs classes

In your defaults.yml (file used instead of parameters.ini, see hatimeria project app/config directory) (
``` yml
  extjs_init_modules:
    module_name: name.of.your.class
```
When you type http://yourhost/extjs/module_name in browser configured extjs class is created.
You don't need another empty action to add new interface

This controller can also test extjs class, just go to http://yourhost/extjs/module_name?test="name.of.your.class".

``` javascript
Ext.define("name.of.your.class", {
    extend: 'Ext.window.Window'
    statistics: {
        testMe: function() {
                    var me = Ext.create(this.prototype.$className);                
                    me.show();
        }
    }
})
```

## Production optimization

This bundle provides easy way to combine all javascripts into one.
Specify locales under extjs config.
Run console command: hatimeria:extjs:compile.
Enable compilation for prod environment.

## Backend application

[CMF Application](https://github.com/hatimeria/hatimeria)

![Example application screenshot](https://github.com/hatimeria/hatimeria/raw/master/app/Resources/doc/images/admin.png)

### Finished

Well, this all to HatimeriaExtJSBundle work. Suggestions, bug reports and observations
are wellcome.