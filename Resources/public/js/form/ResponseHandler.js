Ext.define("HatimeriaCore.form.ResponseHandler", {
    extend: 'HatimeriaCore.response.BaseHandler',
    
    failure: function(form, action)
    {
        var info = '';
        var result = action.result;
        
        this.callParent([result]);
        
        var msg = result.msg;

        if (typeof result.msg == 'object')
        {
            info += "";
            msg.global = null;

            for (var property in msg)
            {
                for (var i in msg[property])
                {
                    var translationKey = 'validators:' + msg[property];
                    if (ExposeTranslation.has(translationKey))
                    {
                        msg[property] = __(translationKey);
                    }

                }

                var field = this.formPanel.getFieldByName(property);
                if (field)
                {
                    field.markInvalid(msg[property]);
                    continue;
                }

                for (i in msg[property])
                {
                    info += msg[property][i] + "<br/>"
                }
            }
        }
        else
        {
            info = msg;
        }

        if (info != '')
        {
            this.displayMessage(info, this.failureWindowTitle || __("form.error.title"));
        }
    }
});