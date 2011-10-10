Ext.define("HatimeriaCore.form.ComboBox", {
    extend: "Ext.form.field.ComboBox",
    alias: 'widget.h-combobox',
    initComponent: function() {
        
        var combo = this;
        
        if(this.store) {
            this.store.on("load", function() {
                if(combo.getValue()) {
                    combo.setValue(combo.getValue());
                    combo.validate();
                }
            });
        }
        
        this.callParent();
    }
})