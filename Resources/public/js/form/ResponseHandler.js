Ext.define("HatimeriaCore.form.ResponseHandler", {
    failure: function(form, action) {
        var info = '';
        var type = typeof action.result.msg
        var msg = action.result.msg;
        
        if(type == 'object') {
            info += "";
            msg.global = null;
            
            for(property in msg) {
                
                for(i in msg[property]) {
                    var translationKey = 'validators:' + msg[property];
                    if(ExposeTranslation.has(translationKey)) {
                        msg[property] = __(translationKey);
                    }
                    
                }
                
                var field = this.formPanel.getFieldByName(property);
                if(field) {
                    field.markInvalid(msg[property]);
                    continue;
                }
                
                for(i in msg[property]) {
                    info += msg[property][i] + "<br/>"
                }
            }
            
        } else {
            info = msg;
        }
        
        if(info != '') {
            Ext.Msg.alert(this.failureWindowTitle || __("form.error.title"), info);
        }
        
    }
});