/* 
 * Preview grid dynamically created with data returned by directFn
 */

Ext.ns("Hatimeria");

Ext.define("Hatimeria.grid.Preview", {
    init: function() {
        var me = this;
        
        if(typeof me.config.directParams == 'undefined') {
            me.config.directParams = {};
        }
        
        me.config.directFn(me.config.directParams, function(response) {me.loadData(response)});
    },
    
    close: function() {
        this.grid.close();
    },
    loadData: function(response) {
        var me = this;
        var first = response.records[0];
        var keys  = Object.keys(first);
        
        storeParams = {
            directFn: me.config.directFn,
            root: 'records',
            autoLoad: false,
            paramsAsHash: true,
            remoteSort: true,
            fields: keys
        };
        
        var store = Ext.create("Ext.data.DirectStore", storeParams);
        store.proxy.extraParams = me.config.directParams;
        store.load();
        
        var columns = [];
        
        for(i in keys) {
            var key = keys[i];
            columns[i] = {dataIndex: key, header: key};
        }
        
        gridParams = {
            store: store,
            title: 'Preview grid',
            columns: columns,
            renderTo: Ext.fly('body'),
            dockedItems: [{
                    xtype: 'pagingtoolbar',
                    store: store,
                    dock: 'bottom',
                    displayInfo: true
            }]
        };
        
        me.grid  = Ext.create("Ext.grid.Panel", gridParams);
    },
    constructor: function(config){
        this.initConfig(config);
        
        return this;
    }
});