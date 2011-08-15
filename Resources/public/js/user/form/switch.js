Ext.onReady(function() {
    Ext.ns('App.User.Form.Switch');

    Ext.define('App.User.Form.Switch', {
    	extend: 'Ext.form.ComboBox',

    	switchUser: 'Switch user: ',
        fieldLabel: this.switchUser,
        store: Ext.create('App.User.Store.All'),
        queryMode: 'remote',
        displayField: 'email',
        valueField: 'email',
        listeners: {
        	scope: this,
        	select: function(field, record) {
        		location.href = Routing.generate('_homepage') + '?_switch_user=' + record[0].get('email');
        	}
        }        
    });
});