Ext.define("HatimeriaCore.form.ResponseHandler", {
    extend: 'HatimeriaCore.response.BaseHandler',
    config: {
        success: function() {},
        formPanel: {}
    },
    
    /**
     * Constructor
     * 
     * @param {} config
     */
    constructor: function(config)
    {
        this.callParent([config]);
        this.initConfig(config);
        
        return this;
    },
    
    /**
     * Manage failure case
     * 
     * @param Ext.form.Base form
     * @param {} action
     */
    failure: function(form, action)
    {
        this.callParent([action.result]);
    },
    
    /**
     * Mark fields as invalid
     * 
     * @param string index
     */
    markMessage: function(index)
    {
        var field = this.getFormPanel().getFieldByName(index);
        if (field)
        {
            field.markInvalid(this.msg[index]);
        }
        else
        {
            this.globalMsg.push(this.msg[index].join(', '));
        }
    }
    
});