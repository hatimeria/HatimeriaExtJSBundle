/**
 * Base Handler
 */
(function() {
    
    Ext.define('HatimeriaCore.response.BaseHandler', {
        config: {
            
            /**
             * Window alert
             * 
             * @var string
             */
            failureWindowTitle: 'Alert'
        },
        
        /**
         * Error message
         * 
         * @var mixed
         */
        msg: undefined,
        
        /**
         * Global errors
         * 
         * @var []
         */
        globalMsg: [],
        
        /**
         * Constructor
         * 
         * @param {} config
         */
        constructor: function(config)
        {
            this.initConfig(config);
            
            return this;
        },
        
        /**
         * Error 
         * 
         * @param {} result
         */
        failure: function(result)
        {
            this.globalMsg = [];
            this.msg = result.msg;
            var translationKey, i;

            if (typeof this.msg == 'object')
            {
                for (var property in this.msg)
                {
                    if (property == 'global')
                    {
                        continue;
                    }
                    
                    for (i in this.msg[property])
                    {
                        translationKey = 'validators:' + this.msg[property][i];
                        if (ExposeTranslation.has(translationKey))
                        {
                            this.msg[property][i] = __(translationKey);
                        }
                    }

                    this.markMessage(property);
                }
            }
            else
            {
                this.globalMsg.push(this.msg);
            }

            if (this.globalMsg.length)
            {
                this.displayMessage();
            }
        },
        
        /**
         * Manage failure case
         * 
         * @param {} result
         */
        displayMessage: function()
        {
            var msg = new Ext.XTemplate([
                '<ul style="list-style-type: square; list-style-position: outsite;">',
                    '<tpl for=".">',
                        '<li>{.}</li>',
                    '</tpl>',
                '</ul>'
            ]).apply(this.globalMsg);
            
            Ext.Msg.alert(this.getFailureWindowTitle(), msg);
        },
        
        /**
         * Marks message in specific place
         * 
         * @param string index
         */
        markMessage: function(index)
        {
            var msg = this.msg[index];
            if (typeof msg == 'object')
            {
                msg = msg.join(', ');
            }
            
            this.globalMsg.push(msg);
        }

    });
    
})();