/**
 * Base Handler
 */
(function() {
    
    Ext.define('HatimeriaCore.response.BaseHandler', {
        
        /**
         * Error 
         * 
         * @param {} result
         */
        failure: function()
        {
            
        },
        
        /**
         * Manage failure case
         * 
         * @param {} result
         */
        displayMessage: function(info, title)
        {
            Ext.Msg.alert(title, info);
        }

    });
    
})();