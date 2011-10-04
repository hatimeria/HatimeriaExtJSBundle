Ext.define("HatimeriaCore.form.BaseForm", {
    extend: "Ext.form.Panel",
    mixins: {
        translationable: 'HatimeriaCore.mixins.Translationable'
    },    
    initComponent: function(config) {
        if(this.submitConfig) {
            this.mountSubmit();
        }
        this.callParent([config]);
    },
    mountSubmit: function() {
        
        var config = this.submitConfig;
        var handler = Ext.create("HatimeriaCore.form.ResponseHandler");
        handler.failureWindowTitle = config.failureWindowTitle;
        handler.success = config.success;
        handler.formPanel = this;
        
        var submitButton = {
            text: config.text,
            handler: function() {
                var form = this.up('form').getForm();
                if (form.isValid()) {
                    form.submit(handler);
                }
            }             
        };
        
        if(!this.buttons) {
            this.buttons = [];
        }
        
        this.buttons.push(submitButton);
    },
    getFieldByName: function(name) {
        var fields = this.getForm()._fields.items;
        for(i in fields) {
            var field = fields[i];
            if(field.name == name) {
                return field;
            }
        }
        
        return false;
    }
});