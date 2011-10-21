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
             * 
             * @var function
             */
            success: undefined,
            
            /**
             * Failure case
             * !!! Error function must return TRUE to run failure method !!!
             * 
             * @var function
             */
            error: undefined,
            
            /**
             * Scope of functions success, error
             * 
             * @var {}
             */
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
            this.callParent([config]);
            this.initConfig(config);
            this.init();
            
            return this;
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
                    if (this.getError().call(scope, result, response))
                    {
                        this.failure(result, response);
                    }
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