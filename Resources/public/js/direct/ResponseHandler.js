/**
 * Response handler
 */
(function() {
    
    Ext.define('HatimeriaCore.direct.ResponseHandler', {
        extend: 'HatimeriaCore.response.BaseHandler',
        config: {
            
            /**
             * Direct callback function
             * 
             * @var function
             */
            fn: function(callback) { callback(); },
            
            /**
             * Request parameters
             * 
             * @var {}
             */
            params: {},
            
            /**
             * Success case
             */
            success: undefined,
            
            /**
             * Failure case
             */
            error: undefined,
            
            scope: undefined
        },
        
        /**
         * Response
         * 
         * @var {}
         */
        response: undefined,
        
        /**
         * Constructor
         * 
         * @param {} config
         */
        constructor: function(config)
        {
            this.initConfig(config);
            this.init();
        },

        /**
         * Call request
         */
        init: function()
        {
            var _this = this;
            if (typeof this.getFn() != 'function')
            {
                throw new Error('Must specify fn parameter');
            }
            
            this.getFn()(this.getParams() || {}, function(result, response) {
                try {
                    _this.onResponse(result, response);
                }
                catch(e)
                {
                    console.error(e);
                }
            });
        },
        
        /**
         * Event: response
         * 
         * @param {} result
         * @param {} response
         */
        onResponse: function(result, response)
        {
            this.response = response;
            var scope = this.getScope() || this;
            
            if (result.success)
            {
                if (typeof this.getSuccess() == 'function')
                {
                    this.getSuccess().call(scope, result);
                }
            }
            else
            {
                if (typeof this.getError() == 'function')
                {
                    this.getError().call(scope, result, response);
                }
                else
                {
                    this.failure(result, response);
                }
            }
        },
        
        /**
         * Manage success:false case
         * 
         * @param {} result
         * @param {} response
         */
        failure: function(result, response)
        {
            this.callParent([result]);
            this.displayMessage(response.msg, this.failureWindowTitle || __("form.error.title"));
        },
        
        /**
         * Response of request
         * 
         * @return {}
         */
        getResponse: function()
        {
            return this.response;
        }
        
    });
    
})();