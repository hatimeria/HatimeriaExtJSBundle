/*
 * Handle common http error codes: 404, 403
 * 
 * @todo 
 * - 500 fatal without output
 */ 

if (typeof window.console != 'object')
{
    window.console = {
        log: Ext.emptyFn,
        error: Ext.emptyFn,
        info: Ext.emptyFn,
        debug: Ext.emptyFn
    };
}

Ext.require('Ext.direct.Manager', function() {
    Ext.direct.Manager.on('event', function(response) {

        // accesible if ext exception is thrown
        var xhr = response.xhr;
        
        if (xhr) {
            var errorWindow = null;
            if(App.Direct.environment == 'dev') {
                errorWindow = Ext.create('App.Direct.DevErrorMessage', {
                    title: __('HatimeriaExtJSBundle:direct.title'),
                    html: xhr.responseText
                });
            } else {
                // user friendly window title
                errorWindow = new App.Direct.UserErrorMessage();
            }

            errorWindow.show();
        }

        // normal response content;
        var result = response.result;

        if(!result) return;

        // only errors are handled
        if (typeof result.success != 'undefined'  && result.success && !result.exception) return;

        switch(result.code)
        {
            case 404:
                // @todo change to something better
                errorWindow = new App.Direct.UserErrorMessage();
                errorWindow.show();
                break;
            case 403:
                if(App.Direct.signinUrl) {
                    window.location = App.Direct.signinUrl;
                } else {
                    new App.Direct.UserErrorMessage({html: __('HatimeriaExtJSBundle:direct.forbidden')});
                }
                break;
        }
    });

    Ext.ns("App.Direct");

    Ext.define("App.Direct.DevErrorMessage", {
        extend: "Ext.Window",
        width: 1000,
        height: 600,
        autoScroll: true,
        bodyStyle: 'background: white; padding: 15px'
    });

    Ext.define("App.Direct.UserErrorMessage", {
        extend: "Ext.Window",
        title: __('HatimeriaExtJSBundle:direct.title'),
        html: __('HatimeriaExtJSBundle:direct.try_later'),
        width: 200,
        height: 50,
        autoScroll: true,
        bodyStyle: 'background: white; padding: 15px'
    });
});