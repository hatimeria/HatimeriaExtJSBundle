/** 
 * Confirm Window
 */
(function() {
    
    Ext.define('HatimeriaCore.window.ConfirmWindow', {
        extend: 'Ext.window.Window',
        
        /**
         * Initializes window
         */
        initComponent: function()
        {
            var config = {
                border: false,
                width: 400,
                resizable: false,
                items: [
                    {
                        border: 0,
                        padding: 10,
                        html: this.initialConfig.msg || "Enter text as {msg: 'text'}"
                    }
                ],
                buttons: [
                    {
                        xtype: 'button',
                        text: 'Tak',
                        scope: this,
                        handler: function() {
                            var callback = (typeof this.initialConfig.onSuccess == "function") ? this.initialConfig.onSuccess : Ext.emptyFn
                            callback();
                            this.destroy();
                        }
                    },
                    {
                        xtype: 'button',
                        text: 'Nie',
                        scope: this,
                        handler: function() {
                            this.destroy();
                        }
                    }
                ]
            };
            
            Ext.apply(this, Ext.apply(config, this.initialConfig));
            this.callParent();
        }
        
    });
    
})();